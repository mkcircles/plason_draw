/**
 * Plascon Lucky Draw - Modern Broadcast Engine
 * Handles 3D mechanical odometer animation, Web Audio synthesis, confetti, and API sync.
 */

(function(window, document) {
    'use strict';

    // Global App Configuration
    const Config = {
        digitHeight: 94, // Matches CSS .digit-item
        spinSpeed: 70,    // Reel change interval (ms)
        stopStagger: 180, // Delay between reel stops (ms)
        defaultRegion: window.PLASCON_INIT_REGION || 'All Regions',
        defaultDrawType: window.PLASCON_INIT_TYPE || 'daily',
        targetWinners: window.PLASCON_INIT_TARGET || 8
    };

    /* ==========================================================================
       Web Audio Synthesizer (Zero External Dependencies)
       ========================================================================== */
    class SoundEngine {
        constructor() {
            this.ctx = null;
            this.muted = false;
            this.initialized = false;
        }

        init() {
            if (!this.initialized) {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    this.ctx = new AudioContext();
                    this.initialized = true;
                } catch (e) {
                    console.warn('Web Audio API not supported in this browser.');
                }
            }
            if (this.ctx && this.ctx.state === 'suspended') {
                this.ctx.resume();
            }
        }

        toggleMute() {
            this.muted = !this.muted;
            return this.muted;
        }

        // Mechanical clicking gear tick
        playTick() {
            if (this.muted || !this.ctx) return;
            try {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(450 + Math.random() * 200, this.ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(80, this.ctx.currentTime + 0.04);
                
                gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.04);
                
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                
                osc.start();
                osc.stop(this.ctx.currentTime + 0.05);
            } catch (e) {}
        }

        // Suspense drumroll / heartbeat riser
        playSuspense() {
            if (this.muted || !this.ctx) return;
            try {
                const now = this.ctx.currentTime;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();

                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(65, now);
                osc.frequency.exponentialRampToValueAtTime(220, now + 1.2);

                gain.gain.setValueAtTime(0.05, now);
                gain.gain.linearRampToValueAtTime(0.18, now + 1.0);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.3);

                osc.connect(gain);
                gain.connect(this.ctx.destination);

                osc.start();
                osc.stop(now + 1.3);
            } catch (e) {}
        }

        // Triumphant victory celebration fanfare
        playFanfare() {
            if (this.muted || !this.ctx) return;
            try {
                const notes = [
                    { f: 523.25, d: 0.15, t: 0.0 }, // C5
                    { f: 659.25, d: 0.15, t: 0.15 }, // E5
                    { f: 783.99, d: 0.20, t: 0.30 }, // G5
                    { f: 1046.50, d: 0.8, t: 0.50 }  // C6 (Triumph)
                ];

                const now = this.ctx.currentTime;
                notes.forEach(n => {
                    const osc = this.ctx.createOscillator();
                    const gain = this.ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(n.f, now + n.t);

                    gain.gain.setValueAtTime(0.2, now + n.t);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + n.t + n.d);

                    osc.connect(gain);
                    gain.connect(this.ctx.destination);

                    osc.start(now + n.t);
                    osc.stop(now + n.t + n.d + 0.05);
                });
            } catch (e) {}
        }
    }

    /* ==========================================================================
       Celebration Confetti Particle Cannon
       ========================================================================== */
    class ConfettiEngine {
        constructor(canvasId) {
            this.canvas = document.getElementById(canvasId);
            this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
            this.particles = [];
            this.animating = false;
            this.resize();
            window.addEventListener('resize', () => this.resize());
        }

        resize() {
            if (!this.canvas) return;
            this.canvas.width = window.innerWidth;
            this.canvas.height = window.innerHeight;
        }

        blast() {
            if (!this.ctx) return;
            this.particles = [];
            const colors = ['#FFC20E', '#0056B3', '#E31837', '#FFD700', '#FFFFFF', '#0B2F64'];
            const count = 180;

            for (let i = 0; i < count; i++) {
                this.particles.push({
                    x: this.canvas.width / 2,
                    y: this.canvas.height / 2,
                    vx: (Math.random() - 0.5) * 28,
                    vy: (Math.random() - 0.7) * 26,
                    size: Math.random() * 12 + 6,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    tilt: Math.random() * 10 - 10,
                    tiltAngle: 0,
                    tiltAngleInc: (Math.random() * 0.07) + 0.05,
                    gravity: 0.35,
                    opacity: 1
                });
            }

            if (!this.animating) {
                this.animating = true;
                this.render();
            }
        }

        render() {
            if (!this.ctx) return;
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            let activeCount = 0;
            for (let i = 0; i < this.particles.length; i++) {
                const p = this.particles[i];
                p.x += p.vx;
                p.y += p.vy;
                p.vy += p.gravity;
                p.vx *= 0.99;
                p.tiltAngle += p.tiltAngleInc;
                p.tilt = Math.sin(p.tiltAngle) * 15;
                p.opacity -= 0.006;

                if (p.opacity > 0 && p.y < this.canvas.height + 50) {
                    activeCount++;
                    this.ctx.save();
                    this.ctx.globalAlpha = Math.max(0, p.opacity);
                    this.ctx.fillStyle = p.color;
                    this.ctx.beginPath();
                    this.ctx.arc(p.x, p.y, p.size / 2, 0, Math.PI * 2);
                    this.ctx.fill();
                    this.ctx.restore();
                }
            }

            if (activeCount > 0) {
                requestAnimationFrame(() => this.render());
            } else {
                this.animating = false;
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            }
        }
    }

    /* ==========================================================================
       Plascon Draw Main Controller
       ========================================================================== */
    class DrawController {
        constructor() {
            this.sound = new SoundEngine();
            this.confetti = new ConfettiEngine('confetti-canvas');
            
            this.isSpinning = false;
            this.isPaused = false;
            this.explicitMode = Boolean(window.PLASCON_EXPLICIT_MODE);
            this.explicitPool = Array.isArray(window.PLASCON_EXPLICIT_NUMBERS)
                ? window.PLASCON_EXPLICIT_NUMBERS.map(String)
                : [];
            this.drawnSet = new Set();
            this.pool = [];
            this.sessionWinners = [];
            this.spinInterval = null;
            this.currentNumber = '0770000000';
            this.maskedMode = false;
            this.defaultPrize = window.PLASCON_DEFAULT_PRIZE || 'Paint & Win Prize';
            this.fileUsed = window.PLASCON_FILE_USED || 'live_draw_ui';
            
            this.initDOM();
            this.initReels();
            this.bindEvents();
            this.loadPool();
            this.loadHistory();
        }

        initDOM() {
            this.btnAction = document.getElementById('btn-main-action');
            this.btnNext = document.getElementById('btn-next-winner');
            this.btnManageNumbers = document.getElementById('btn-manage-numbers');
            this.btnSound = document.getElementById('btn-toggle-sound');
            this.btnFullscreen = document.getElementById('btn-toggle-fullscreen');
            this.btnPrivacy = document.getElementById('btn-toggle-privacy');
            this.btnHistory = document.getElementById('btn-toggle-history');
            this.selectRegion = document.getElementById('select-region');
            this.selectDrawType = document.getElementById('select-draw-type');
            this.selectCount = document.getElementById('select-target-count');
            this.regionDisplay = document.getElementById('current-region-display');
            this.poolStatusBadge = document.getElementById('pool-status-badge');
            this.poolCountDisplay = document.getElementById('current-pool-count');
            this.poolCountHeader = document.getElementById('pool-count-header');
            this.campaignHeadline = document.getElementById('campaign-headline');
            this.stageWrapper = document.getElementById('odometer-wrapper');
            this.winnerModal = document.getElementById('winner-modal');
            this.historyDrawer = document.getElementById('history-drawer');
            this.winnerList = document.getElementById('winner-list-items');
            this.btnExport = document.getElementById('btn-export-csv');
            this.btnModalClose = document.getElementById('btn-modal-close');
            this.btnModalNext = document.getElementById('btn-modal-next');

            // Explicit Numbers Dialog Elements
            this.numbersDialog = document.getElementById('numbers-dialog');
            this.numbersInput = document.getElementById('explicit-numbers-input');
            this.numbersFileInput = document.getElementById('numbers-file-upload');
            this.btnClearNumbers = document.getElementById('btn-clear-numbers');
            this.dialogParsedCount = document.getElementById('dialog-parsed-count');

            // Winner Modal Fields
            this.modalPhone = document.getElementById('modal-winner-phone');
            this.modalRegion = document.getElementById('modal-winner-region');
            this.modalPrize = document.getElementById('modal-winner-prize');
        }

        // Parse raw delimited string into normalized unique MSISDNs
        parseExplicitNumbers(rawText) {
            if (!rawText || typeof rawText !== 'string') return [];
            const tokens = rawText.split(/[\s,;|\r\n]+/);
            const seen = new Set();
            const result = [];

            for (let i = 0; i < tokens.length; i++) {
                let digits = tokens[i].replace(/[^0-9]/g, '');
                if (!digits) continue;

                if (digits.length === 9 && digits.startsWith('7')) {
                    digits = '256' + digits;
                } else if (digits.length === 10 && digits.startsWith('0')) {
                    digits = '256' + digits.substring(1);
                }

                if (digits.length >= 9 && !seen.has(digits)) {
                    seen.add(digits);
                    result.push(digits);
                }
            }
            return result;
        }

        // Format phone number to Ugandan standard (e.g. 0772 123 456)
        formatPhoneClient(phone, masked = false) {
            let clean = String(phone || '').replace(/[^0-9]/g, '');
            if (clean.startsWith('256') && clean.length === 12) {
                clean = '0' + clean.substring(3);
            }
            if (clean.length === 10) {
                if (masked) {
                    return clean.substring(0, 4) + ' ••• ' + clean.substring(7);
                }
                return clean.substring(0, 4) + ' ' + clean.substring(4, 7) + ' ' + clean.substring(7);
            }
            return clean;
        }

        updatePoolStatusUI() {
            const remainingCount = this.explicitMode ? this.pool.length : this.explicitPool.length;
            if (this.poolCountHeader) {
                this.poolCountHeader.textContent = remainingCount;
            }
            if (this.poolCountDisplay) {
                this.poolCountDisplay.textContent = `${remainingCount} NUMBER${remainingCount === 1 ? '' : 'S'}`;
            }
            if (this.poolStatusBadge) {
                this.poolStatusBadge.style.display = this.explicitMode ? 'inline-flex' : 'none';
            }
            if (this.btnManageNumbers) {
                this.btnManageNumbers.classList.toggle('active', this.explicitMode);
            }
        }

        initReels() {
            const reelsContainer = document.getElementById('reels-board');
            if (!reelsContainer) return;

            // Generate 10 digit slots (e.g. 0 7 X X  X X X  X X X)
            reelsContainer.innerHTML = '';

            // Country prefix Uganda +256 badge
            const badge = document.createElement('div');
            badge.className = 'country-prefix-badge';
            badge.innerHTML = `<span class="flag-icon">🇺🇬</span><span>+256</span>`;
            reelsContainer.appendChild(badge);

            this.reelElements = [];

            // 10 digits
            for (let i = 0; i < 10; i++) {
                // Add spacer after index 1 (07..) and index 4 (07xx ...)
                if (i === 1 || i === 4 || i === 7) {
                    const spacer = document.createElement('div');
                    spacer.className = 'reel-spacer';
                    reelsContainer.appendChild(spacer);
                }

                const slot = document.createElement('div');
                slot.className = 'digit-slot';
                slot.dataset.index = i;

                const strip = document.createElement('div');
                strip.className = 'digit-strip';

                // Fill strip with 4 repetitions of 0-9 to allow seamless wrap
                let digitsHTML = '';
                for (let rep = 0; rep < 4; rep++) {
                    for (let d = 0; d <= 9; d++) {
                        digitsHTML += `<div class="digit-item">${d}</div>`;
                    }
                }
                strip.innerHTML = digitsHTML;
                slot.appendChild(strip);
                reelsContainer.appendChild(slot);

                this.reelElements.push({
                    slot: slot,
                    strip: strip,
                    currentDigit: 0
                });
            }

            this.setReelDigits(this.currentNumber, false);
        }

        // Update reels to display target 10-digit number
        setReelDigits(phoneNumber, animate = true) {
            // Normalize to 10 digits (e.g., 25677... -> 077...)
            let digits = String(phoneNumber || '').replace(/[^0-9]/g, '');
            if (digits.startsWith('256') && digits.length === 12) {
                digits = '0' + digits.substring(3);
            }
            if (digits.length < 10) {
                digits = digits.padStart(10, '0');
            }

            this.currentNumber = digits;
            const itemH = 94; // Height per digit item

            for (let i = 0; i < 10; i++) {
                const targetDigit = parseInt(digits[i], 10) || 0;
                const reel = this.reelElements[i];

                if (!reel) continue;

                // Position within strip (use 2nd block of 0-9 for clean transitions)
                const targetY = -(targetDigit + 10) * itemH;

                if (!animate) {
                    reel.strip.style.transition = 'none';
                    reel.strip.style.transform = `translateY(${targetY}px)`;
                } else {
                    reel.strip.style.transition = 'transform 0.4s cubic-bezier(0.12, 0.8, 0.32, 1)';
                    reel.strip.style.transform = `translateY(${targetY}px)`;
                }
                reel.currentDigit = targetDigit;
            }
        }

        // Fetch draw pool from explicit numbers or API
        async loadPool() {
            if (this.explicitMode) {
                this.pool = this.explicitPool.filter(num => !this.drawnSet.has(num));
                this.updatePoolStatusUI();
                if (this.pool.length > 0) {
                    this.setReelDigits(this.pool[0], false);
                }
                return;
            }

            const region = this.selectRegion ? this.selectRegion.value : Config.defaultRegion;
            try {
                const res = await fetch(`api/pool.php?region=${encodeURIComponent(region)}&limit=300`);
                const data = await res.json();
                if (data.status === 'success' && data.numbers.length > 0) {
                    this.pool = data.numbers;
                    // Set display to first number
                    this.setReelDigits(this.pool[0], false);
                }
            } catch (e) {
                console.error('Error fetching pool:', e);
            }
            this.updatePoolStatusUI();
        }

        // Fetch past winners history
        async loadHistory() {
            if (this.explicitMode && this.sessionWinners.length > 0 && this.winnerList) {
                this.winnerList.innerHTML = '';
                this.sessionWinners.forEach(w => this.appendWinnerToList(w));
                return;
            }
            try {
                const region = this.selectRegion ? this.selectRegion.value : '';
                const res = await fetch(`api/history.php?region=${encodeURIComponent(region)}&limit=50`);
                const data = await res.json();
                if (data.status === 'success' && this.winnerList) {
                    this.winnerList.innerHTML = '';
                    data.winners.forEach(w => this.appendWinnerToList(w));
                }
            } catch (e) {
                console.error('Error fetching history:', e);
            }
        }

        // Start Reel Spinning
        startSpin() {
            this.sound.init();
            if (this.isSpinning) return;

            if (this.explicitMode) {
                this.pool = this.explicitPool.filter(num => !this.drawnSet.has(num));
                this.updatePoolStatusUI();
                if (this.pool.length === 0) {
                    alert('No remaining numbers in the explicit pool! Please load or paste numbers to draw from.');
                    if (this.numbersDialog && typeof this.numbersDialog.showModal === 'function') {
                        this.numbersDialog.showModal();
                    }
                    return;
                }
            }

            this.isSpinning = true;
            this.stageWrapper.classList.add('spinning');
            this.stageWrapper.classList.remove('winner-celebration');
            
            this.btnAction.classList.add('btn-stop');
            this.btnAction.innerHTML = `<span>⏹ STOP & REVEAL</span>`;

            // Rapidly cycle digits with blur
            this.reelElements.forEach(r => r.strip.classList.add('blur-motion'));

            let counter = 0;
            this.spinInterval = setInterval(() => {
                // Pick random phone from pool or generate random digits
                const randNum = this.pool.length > 0 
                    ? this.pool[Math.floor(Math.random() * this.pool.length)]
                    : '07' + Math.floor(10000000 + Math.random() * 90000000);

                this.setReelDigits(randNum, false);
                counter++;

                if (counter % 3 === 0) {
                    this.sound.playTick();
                }
            }, Config.spinSpeed);
        }

        // Pick a random index using Web Crypto API when available
        pickRandomIndex(max) {
            if (max <= 1) return 0;
            if (window.crypto && window.crypto.getRandomValues) {
                const arr = new Uint32Array(1);
                window.crypto.getRandomValues(arr);
                return arr[0] % max;
            }
            return Math.floor(Math.random() * max);
        }

        // Stop Reel and Reveal Winner Atomically
        async stopAndReveal() {
            if (!this.isSpinning) return;
            clearInterval(this.spinInterval);

            this.btnAction.disabled = true;
            this.btnAction.innerHTML = `<span>⏳ REVEALING...</span>`;
            this.sound.playSuspense();

            const region = this.selectRegion ? this.selectRegion.value : 'National';
            const drawType = this.selectDrawType ? this.selectDrawType.value : 'daily';
            const prizeName = drawType === 'tukutuku' ? 'Tukutuku Prize' : this.defaultPrize;

            // Explicit Numbers Mode: select winner directly from the provided numbers pool
            if (this.explicitMode) {
                const eligible = this.explicitPool.filter(num => !this.drawnSet.has(num));
                if (eligible.length === 0) {
                    alert('No eligible numbers remaining in the provided list.');
                    this.resetControls();
                    return;
                }

                const chosenMsisdn = eligible[this.pickRandomIndex(eligible.length)];
                this.drawnSet.add(chosenMsisdn);
                this.pool = this.explicitPool.filter(num => !this.drawnSet.has(num));
                this.updatePoolStatusUI();

                // Attempt to record in DB as well, but do not fail if DB is offline
                try {
                    const res = await fetch('api/draw.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            msisdn: chosenMsisdn,
                            region: region,
                            draw_type: drawType,
                            prize: prizeName,
                            file_used: this.fileUsed,
                            explicit_mode: true
                        })
                    });
                    const data = await res.json();
                    if (data && data.status === 'success' && data.winner) {
                        this.celebrateWinner(data.winner);
                        return;
                    }
                } catch (e) {
                    // Proceed with client-side winner object if database / API is unreachable
                }

                const fallbackWinner = {
                    msisdn: chosenMsisdn,
                    formatted: this.formatPhoneClient(chosenMsisdn, false),
                    masked: this.formatPhoneClient(chosenMsisdn, true),
                    region: region || 'National',
                    draw_type: drawType,
                    prize: prizeName,
                    drawn_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
                };
                this.celebrateWinner(fallbackWinner);
                return;
            }

            try {
                // Call atomic draw API endpoint
                const res = await fetch('api/draw.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        region: region,
                        draw_type: drawType,
                        prize: prizeName
                    })
                });

                const data = await res.json();
                if (data.status !== 'success') {
                    alert('Draw Error: ' + (data.message || 'Could not select winner.'));
                    this.resetControls();
                    return;
                }

                const winner = data.winner;
                this.celebrateWinner(winner);

            } catch (e) {
                console.error('Draw API Error:', e);
                alert('Connection error during draw. Please check database connection.');
                this.resetControls();
            }
        }

        // Animate Reel Deceleration to Winner Digits & Trigger Celebration
        celebrateWinner(winner) {
            const finalPhone = String(winner.msisdn || '');
            const cleanDigits = finalPhone.replace(/^256/, '0');
            const itemH = 94;

            // Sequentially stop reels from left to right for dramatic suspense
            this.reelElements.forEach((reel, index) => {
                setTimeout(() => {
                    reel.strip.classList.remove('blur-motion');
                    const digit = parseInt(cleanDigits[index], 10) || 0;
                    const targetY = -(digit + 20) * itemH;

                    reel.strip.style.transition = 'transform 0.6s cubic-bezier(0.15, 0.9, 0.25, 1)';
                    reel.strip.style.transform = `translateY(${targetY}px)`;
                    this.sound.playTick();

                    // When last digit locks in:
                    if (index === this.reelElements.length - 1) {
                        setTimeout(() => {
                            this.onWinnerLocked(winner);
                        }, 500);
                    }
                }, index * Config.stopStagger);
            });
        }

        onWinnerLocked(winner) {
            this.isSpinning = false;
            this.stageWrapper.classList.remove('spinning');
            this.stageWrapper.classList.add('winner-celebration');

            // Victory Sounds and Confetti Explosion
            this.sound.playFanfare();
            this.confetti.blast();

            // Record in session
            this.sessionWinners.push(winner);
            this.appendWinnerToList(winner);

            // Populate & Display Winner Modal
            const displayPhone = this.maskedMode ? winner.masked : winner.formatted;
            this.modalPhone.textContent = displayPhone;
            this.modalRegion.textContent = winner.region;
            this.modalPrize.textContent = winner.prize || 'Plascon Paint & Win Prize';
            
            setTimeout(() => {
                this.winnerModal.classList.add('open');
            }, 300);

            this.resetControls();
        }

        resetControls() {
            this.isSpinning = false;
            this.btnAction.disabled = false;
            this.btnAction.classList.remove('btn-stop');
            this.btnAction.innerHTML = `<span>▶ START SPIN</span>`;
        }

        appendWinnerToList(winner) {
            if (!this.winnerList) return;
            const li = document.createElement('li');
            li.className = 'winner-list-item';
            li.innerHTML = `
                <div>
                    <div class="winner-list-phone">${this.maskedMode ? winner.masked : winner.formatted}</div>
                    <div class="winner-list-details">${winner.region} • ${winner.draw_type || 'daily'}</div>
                </div>
                <span class="badge-tag">🏆 WINNER</span>
            `;
            this.winnerList.prepend(li);
        }

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.warn(`Error attempting to enable fullscreen: ${err.message}`);
                });
                document.body.classList.add('tv-broadcast-mode');
                if (this.btnFullscreen) this.btnFullscreen.classList.add('active');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                document.body.classList.remove('tv-broadcast-mode');
                if (this.btnFullscreen) this.btnFullscreen.classList.remove('active');
            }
        }

        togglePrivacy() {
            this.maskedMode = !this.maskedMode;
            if (this.btnPrivacy) {
                this.btnPrivacy.classList.toggle('active', this.maskedMode);
                this.btnPrivacy.innerHTML = this.maskedMode ? `<span>👁️ FULL NUMBERS</span>` : `<span>🔒 PRIVACY MASK</span>`;
            }
            this.loadHistory();
        }

        exportSessionWinnersCSV() {
            const headers = ['No.', 'Phone Number (Standard)', 'Formatted (Local)', 'Region', 'Draw Type', 'Prize Won', 'Drawn At'];
            const rows = this.sessionWinners.map((w, idx) => [
                idx + 1,
                w.msisdn,
                w.formatted,
                w.region || 'National',
                w.draw_type || 'tukutuku',
                w.prize || 'Tukutuku Prize',
                w.drawn_at || ''
            ]);
            const csvLines = [headers, ...rows].map(row =>
                row.map(val => `"${String(val).replace(/"/g, '""')}"`).join(',')
            );
            const csvContent = '\uFEFF' + csvLines.join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `plascon_tukutuku_winners_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        bindEvents() {
            // Main Button Click (Start / Stop)
            if (this.btnAction) {
                this.btnAction.addEventListener('click', () => {
                    if (!this.isSpinning) {
                        this.startSpin();
                    } else {
                        this.stopAndReveal();
                    }
                });
            }

            // Next Winner Button
            if (this.btnNext) {
                this.btnNext.addEventListener('click', () => {
                    this.startSpin();
                });
            }

            // Sound Toggle
            if (this.btnSound) {
                this.btnSound.addEventListener('click', () => {
                    this.sound.init();
                    const muted = this.sound.toggleMute();
                    this.btnSound.classList.toggle('active', !muted);
                    this.btnSound.innerHTML = muted ? `<span>🔇 SOUND OFF</span>` : `<span>🔊 SOUND ON</span>`;
                });
            }

            // Fullscreen Toggle
            if (this.btnFullscreen) {
                this.btnFullscreen.addEventListener('click', () => this.toggleFullscreen());
            }

            // Privacy Mask Toggle
            if (this.btnPrivacy) {
                this.btnPrivacy.addEventListener('click', () => this.togglePrivacy());
            }

            // History Drawer Toggle
            if (this.btnHistory) {
                this.btnHistory.addEventListener('click', () => {
                    this.historyDrawer.classList.toggle('open');
                });
            }

            const drawerClose = document.getElementById('btn-close-drawer');
            if (drawerClose) {
                drawerClose.addEventListener('click', () => {
                    this.historyDrawer.classList.remove('open');
                });
            }

            // Export CSV Button
            if (this.btnExport) {
                this.btnExport.addEventListener('click', () => {
                    if (this.explicitMode && this.sessionWinners.length > 0) {
                        this.exportSessionWinnersCSV();
                        return;
                    }
                    const region = this.selectRegion ? this.selectRegion.value : '';
                    window.location.href = `api/export.php?region=${encodeURIComponent(region)}`;
                });
            }

            // Manage Explicit Numbers Dialog
            if (this.btnManageNumbers && this.numbersDialog) {
                this.btnManageNumbers.addEventListener('click', () => {
                    if (this.numbersInput && this.dialogParsedCount) {
                        const currentParsed = this.parseExplicitNumbers(this.numbersInput.value);
                        this.dialogParsedCount.textContent = currentParsed.length;
                    }
                    if (typeof this.numbersDialog.showModal === 'function') {
                        this.numbersDialog.showModal();
                    }
                });
            }

            if (this.numbersInput && this.dialogParsedCount) {
                this.numbersInput.addEventListener('input', () => {
                    const parsed = this.parseExplicitNumbers(this.numbersInput.value);
                    this.dialogParsedCount.textContent = parsed.length;
                });
            }

            if (this.btnClearNumbers && this.numbersInput) {
                this.btnClearNumbers.addEventListener('click', () => {
                    this.numbersInput.value = '';
                    if (this.dialogParsedCount) {
                        this.dialogParsedCount.textContent = '0';
                    }
                    this.numbersInput.focus();
                });
            }

            if (this.numbersFileInput && this.numbersInput) {
                this.numbersFileInput.addEventListener('change', (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (evt) => {
                        const content = String(evt.target.result || '');
                        const parsed = this.parseExplicitNumbers(content);
                        this.numbersInput.value = parsed.join('\n');
                        if (this.dialogParsedCount) {
                            this.dialogParsedCount.textContent = parsed.length;
                        }
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                });
            }

            if (this.numbersDialog) {
                this.numbersDialog.addEventListener('close', () => {
                    if (this.numbersDialog.returnValue === 'apply' && this.numbersInput) {
                        const parsed = this.parseExplicitNumbers(this.numbersInput.value);
                        this.explicitPool = parsed;
                        this.explicitMode = true;
                        this.drawnSet.clear();
                        this.loadPool();
                    }
                });
            }

            // Region Dropdown Change
            if (this.selectRegion) {
                this.selectRegion.addEventListener('change', () => {
                    if (this.regionDisplay) {
                        this.regionDisplay.textContent = this.selectRegion.value.toUpperCase();
                    }
                    this.loadPool();
                    this.loadHistory();
                });
            }

            // Modal Close & Next Buttons
            if (this.btnModalClose) {
                this.btnModalClose.addEventListener('click', () => {
                    this.winnerModal.classList.remove('open');
                });
            }

            if (this.btnModalNext) {
                this.btnModalNext.addEventListener('click', () => {
                    this.winnerModal.classList.remove('open');
                    setTimeout(() => {
                        this.startSpin();
                    }, 400);
                });
            }

            // Keyboard Shortcuts (Enter, Space, N, F, M, Esc)
            window.addEventListener('keydown', (e) => {
                // Ignore if typing in a form or select
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
                    return;
                }

                if (e.code === 'Space' || e.key === 'Enter') {
                    e.preventDefault();
                    if (!this.isSpinning) {
                        this.startSpin();
                    } else {
                        this.stopAndReveal();
                    }
                } else if (e.key === 'n' || e.key === 'N') {
                    e.preventDefault();
                    if (this.winnerModal.classList.contains('open')) {
                        this.winnerModal.classList.remove('open');
                    }
                    this.startSpin();
                } else if (e.key === 'f' || e.key === 'F') {
                    e.preventDefault();
                    this.toggleFullscreen();
                } else if (e.key === 'm' || e.key === 'M') {
                    e.preventDefault();
                    if (this.btnSound) this.btnSound.click();
                } else if (e.key === 'Escape') {
                    if (this.winnerModal.classList.contains('open')) {
                        this.winnerModal.classList.remove('open');
                    }
                    if (this.historyDrawer.classList.contains('open')) {
                        this.historyDrawer.classList.remove('open');
                    }
                }
            });
        }
    }

    // Auto-bootstrap on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        window.PlasconApp = new DrawController();
    });

})(window, document);
