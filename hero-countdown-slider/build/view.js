(function(){
	function parseProps(root){
		try { return JSON.parse(root.getAttribute('data-props') || '{}'); }
		catch(e){ return {}; }
	}

	function isExpired(isoString){
		if(!isoString) return false;
		const now = new Date();
		const end = new Date(isoString);
		return end.getTime() <= now.getTime();
	}

	function timeLeft(targetIso){
		if(!targetIso) return null;
		const now = new Date().getTime();
		const t = new Date(targetIso).getTime() - now;
		if (t <= 0) return { d:0,h:0,m:0,s:0, done:true };
		const d = Math.floor(t/(1000*60*60*24));
		const h = Math.floor((t%(1000*60*60*24))/(1000*60*60));
		const m = Math.floor((t%(1000*60*60))/(1000*60));
		const s = Math.floor((t%(1000*60))/1000);
		return { d,h,m,s, done:false };
	}

	function buildSlideEl(s, idx){
		const slide = document.createElement('div');
		slide.className = 'hcs-slide';
		slide.setAttribute('role', 'group');
		slide.setAttribute('aria-roledescription', 'slide');
		slide.setAttribute('aria-label', (idx+1).toString());

		// Background image
		const fig = document.createElement('figure');
		fig.className = 'hcs-bg';
		fig.style.backgroundImage = 'url("'+ (s.imageUrl || '') +'")';
		slide.appendChild(fig);

		// Content
		const content = document.createElement('div');
		content.className = 'hcs-content';

		if (s.showLogo && s.logoUrl) {
			const logo = document.createElement('img');
			logo.className = 'hcs-logo';
			logo.src = s.logoUrl;
			logo.alt = '';
			content.appendChild(logo);
		} else {
			if (s.title) {
				const h = document.createElement('h2');
				h.className = 'hcs-title';
				h.textContent = s.title;
				content.appendChild(h);
			}
			if (s.subtitle) {
				const p = document.createElement('p');
				p.className = 'hcs-subtitle';
				p.textContent = s.subtitle;
				content.appendChild(p);
			}
		}

		// Countdown
		const countdownWrap = document.createElement('div');
		countdownWrap.className = 'hcs-countdown';
		if (s.countdownTo) {
			const span = document.createElement('span');
			span.className = 'hcs-countdown-value';
			countdownWrap.appendChild(span);
			updateCountdown(span, s.countdownTo);
			startCountdown(span, s.countdownTo);
		}
		content.appendChild(countdownWrap);

		// CTA
		if (s.ctaLabel && s.ctaUrl) {
			const a = document.createElement('a');
			a.className = 'hcs-cta';
			a.textContent = s.ctaLabel;
			a.href = s.ctaUrl;
			a.rel = s.ctaNofollow ? 'nofollow noopener' : 'noopener';
			a.target = '_self';
			content.appendChild(a);
		}

		slide.appendChild(content);
		return slide;
	}

	function updateCountdown(el, iso){
		const t = timeLeft(iso);
		if(!t){ el.textContent=''; return; }
		if(t.done){
			el.textContent = '00:00:00';
			return;
		}
		const dd = t.d > 0 ? (t.d + 'd ') : '';
		const hh = String(t.h).padStart(2,'0');
		const mm = String(t.m).padStart(2,'0');
		const ss = String(t.s).padStart(2,'0');
		el.textContent = dd + hh + ':' + mm + ':' + ss;
	}

	function startCountdown(el, iso){
		function tick(){
			updateCountdown(el, iso);
			if (timeLeft(iso)?.done) return;
			el.__hcsTimer = setTimeout(tick, 1000);
		}
		tick();
	}

	function initSlider(root){
		const props = parseProps(root);
		const slidesData = (props.slides || [])
			.filter(s => s.isActive !== false)
			.filter(s => !isExpired(s.expiresAt));

		root.classList.add('hcs-mounted');
		root.style.setProperty('--hcs-height', props.height || '60vh');
		root.style.setProperty('--hcs-radius', props.borderRadius || '24px');

		const track = document.createElement('div');
		track.className = 'hcs-track';
		root.appendChild(track);

		slidesData.forEach((s, i) => track.appendChild( buildSlideEl(s, i) ));

		// Nav
		let idx = 0;
		const go = (n) => {
			idx = (n + slidesData.length) % slidesData.length;
			track.style.transform = 'translateX(' + (-idx * 100) + '%)';
			updateDots();
		};

		// Arrows
		if (props.showArrows && slidesData.length > 1){
			const prev = document.createElement('button');
			prev.className = 'hcs-arrow hcs-prev';
			prev.setAttribute('aria-label', 'Vorherige Slide');
			prev.innerHTML = '&#10094;';
			prev.addEventListener('click', () => go(idx - 1));
			root.appendChild(prev);

			const next = document.createElement('button');
			next.className = 'hcs-arrow hcs-next';
			next.setAttribute('aria-label', 'Nächste Slide');
			next.innerHTML = '&#10095;';
			next.addEventListener('click', () => go(idx + 1));
			root.appendChild(next);
		}

		// Dots
		let dots = null;
		function updateDots(){
			if(!dots) return;
			[...dots.children].forEach((d, i) => d.classList.toggle('is-active', i === idx));
		}
		if (props.showDots && slidesData.length > 1) {
			dots = document.createElement('div');
			dots.className = 'hcs-dots';
			slidesData.forEach((_, i) => {
				const b = document.createElement('button');
				b.className = 'hcs-dot';
				b.setAttribute('aria-label', 'Zu Slide ' + (i+1));
				b.addEventListener('click', () => go(i));
				dots.appendChild(b);
			});
			root.appendChild(dots);
			updateDots();
		}

		// Autoplay
		if (props.autoplay && slidesData.length > 1) {
			let timer = null;
			const start = () => { timer = setInterval(() => go(idx + 1), props.autoplayDelay || 5000); };
			const stop  = () => { if(timer) clearInterval(timer); timer = null; };
			root.addEventListener('mouseenter', stop);
			root.addEventListener('mouseleave', start);
			start();
		}

		// Responsive: swipe
		let startX = 0, delta = 0;
		track.addEventListener('touchstart', (e)=>{ startX = e.touches[0].clientX; delta = 0; }, {passive:true});
		track.addEventListener('touchmove',  (e)=>{ delta = e.touches[0].clientX - startX; }, {passive:true});
		track.addEventListener('touchend',   ()=>{ if(Math.abs(delta) > 60){ go(idx + (delta < 0 ? 1 : -1)); } });

		// Fallback: wenn keine Slides verfügbar
		if (slidesData.length === 0) {
			root.innerHTML = '<div class="hcs-empty">Keine aktiven Slides</div>';
		}
	}

	document.addEventListener('DOMContentLoaded', function(){
		document.querySelectorAll('.hcs-slider').forEach(initSlider);
	});
})();
