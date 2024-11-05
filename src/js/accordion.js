import Accordion from 'accordion-js';

const accordions = Array.from( document.querySelectorAll( '.accordion' ) );
new Accordion(
	accordions,
	{
		duration: 250,
		openOnInit: [0],
	}
);