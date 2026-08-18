(function () {
	'use strict';

	// Erst ab hier darf CSS Inhalte für die Scroll-Animation ausblenden
	// (progressive enhancement, siehe .js [data-animate] in style.css).
	document.documentElement.classList.add('js');

	// Mobiles Menü: Klick, Escape und Klick außerhalb schließen es wieder.
	var toggle = document.querySelector('.menu-toggle');
	var nav = document.querySelector('#site-navigation');

	if (toggle && nav) {
		var setOpen = function (open) {
			toggle.setAttribute('aria-expanded', String(open));
			nav.classList.toggle('is-open', open);
		};

		toggle.addEventListener('click', function () {
			setOpen(toggle.getAttribute('aria-expanded') !== 'true');
		});

		nav.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				setOpen(false);
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
				setOpen(false);
				toggle.focus();
			}
		});

		document.addEventListener('click', function (event) {
			if (
				toggle.getAttribute('aria-expanded') === 'true' &&
				!nav.contains(event.target) &&
				!toggle.contains(event.target)
			) {
				setOpen(false);
			}
		});
	}

	// Dezentes Einblenden markierter Abschnitte beim Scrollen.
	// Reagiert nicht, wenn reduzierte Bewegung gewünscht ist.
	var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var animatedItems = document.querySelectorAll('[data-animate]');

	if (!prefersReducedMotion && animatedItems.length && 'IntersectionObserver' in window) {
		var groups = document.querySelectorAll('[data-animate-group]');
		groups.forEach(function (group) {
			Array.prototype.forEach.call(group.children, function (child, index) {
				child.style.setProperty('--ec-i', index);
			});
		});

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						observer.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.2, rootMargin: '0px 0px -40px 0px' }
		);

		animatedItems.forEach(function (item) {
			observer.observe(item);
		});
	} else {
		animatedItems.forEach(function (item) {
			item.classList.add('is-visible');
		});
	}
}());
