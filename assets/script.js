jQuery( document ).ready(
	function ($) {
		/**
		 * Trigger filtering for resources.
		 * Handles collecting form data, updating the "Filters Used" section,
		 * and sending an AJAX request to fetch filtered results.
		 *
		 * @param {number} paged The current page number (default is 1).
		 */
		window.triggerFiltering = function (paged = 1) {
			// Attach to the window object
			let searchTerm     = $( '#search' ).val();
			let appliedFilters = [];

			if (searchTerm) {
				appliedFilters.push(
					` <span class = "filter-item" data-type = "search" data-value = "${searchTerm}">
					<strong>Search: </strong> ${searchTerm}
					<button class = "remove-filter" aria-label = "Remove search term">×</button>
					</span>`
				);
			}

			// Collect selected taxonomy filters dynamically
			let taxonomyFilters = {};
			$( 'input[type="checkbox"]:checked' ).each(
				function () {
					let taxonomy = $( this ).attr( 'name' ).replace( '[]', '' ); // Extract taxonomy name

					if ( ! taxonomyFilters[taxonomy]) {
						taxonomyFilters[taxonomy] = [];
					}

					taxonomyFilters[taxonomy].push(
						{
							value: $( this ).val(),
							text: $( this ).closest( 'label' ).text().trim() // Get the text associated with the checkbox
						}
					);
				}
			);

			const toTitleCase = (phrase) => {
				return phrase
				.toLowerCase()
				.split( '_' )
				.map( word => word.charAt( 0 ).toUpperCase() + word.slice( 1 ) )
				.join( ' ' );
			};

			// Build applied filters for display
			let dropdownFilters = [];
			let finalFilters    = {};

			for (let taxonomy in taxonomyFilters) {
				taxonomyFilters[taxonomy].forEach(
					function (term) {
						let taxName = toTitleCase( taxonomy );

						const filterItemTemplate = document.querySelector('#filter-item-template');

						if (filterItemTemplate) { // use template if available
							const newFilterItem = filterItemTemplate.content.cloneNode(true);
							const serializer = new XMLSerializer();

							newFilterItem.querySelector('.filter-item').dataset.type = taxonomy;
							newFilterItem.querySelector('.filter-item').dataset.value = term.value;
							newFilterItem.querySelector('.filter-label').innerHTML = `${taxName}: `;
							newFilterItem.querySelector('.filter-value').textContent = term.text;

							appliedFilters.push(serializer.serializeToString(newFilterItem));
						} 
						// fallback for older versions
						else {
							appliedFilters.push(
								`<span class = "filter-item" data-type = "${taxonomy}" data-value = "${term.value}">
								<strong>${taxName}: </strong>${term.text}
								<button class = "remove-filter" aria-label = "Remove ${term.text}">×</button>
								</span>`
							);
						}

						dropdownFilters.push( term.text );

						if ( ! finalFilters[taxonomy]) {
							finalFilters[taxonomy] = [];
						}

						finalFilters[taxonomy].push(
							term.value,
						);

						// $(`#${taxonomy}_text`).html(
						// dropdownFilters ? dropdownFilters.join(', ') : taxName
						// );
					}
				);
			}

			$( '#applied-filters' ).html( appliedFilters.length ? appliedFilters.join( ' ' ) : 'None' );

			let formData = {
				action: 'filter_resources',
				nonce: resourceFilterAjax.nonce,
				search: searchTerm,
				paged: paged,
				sort_order: $( '#sortOrder' ).val(),
				...finalFilters, // Include taxonomy filters dynamically
			};

			// Perform AJAX request
			$.post(
				resourceFilterAjax.ajaxurl,
				formData,
				function (response) {
					response = JSON.parse( response );

					$( '#resource-results' ).html( response.html );
					$( '#result-count' ).text( response.count || 0 );

					// Update pagination
					if (response.pagination && response.pagination.length > 0) {
						$( '.pagination' ).html( response.pagination.join( '' ) );
					} else {
						$( '.pagination' ).html( '' );
					}
				}
			);
		};

		// Handle sort order change
		$( '#sortOrder' ).on(
			'change',
			function () {
				triggerFiltering();
			}
		);

		// Trigger filtering when dropdowns or checkboxes change
		$( '#resource-filter select, #resource-filter input[type="checkbox"]' ).on(
			'change',
			function () {
				triggerFiltering( 1 );
			}
		);

		// Allow the search field to be submitted manually
		$( '#resource-filter' ).on(
			'submit',
			function (e) {
				e.preventDefault();
				triggerFiltering( 1 );
			}
		);

		// Handle pagination link clicks.
		$( document ).on(
			'click',
			'.pagination a',
			function (e) {
				e.preventDefault();

				let pagedMatch = $( this ).attr( 'href' ).match( /paged=(\d+)/ );
				let paged      = pagedMatch ? parseInt( pagedMatch[1], 10 ) : 1;

				triggerFiltering( paged );

				// Scroll to the results target and move focus (twice) after going to new page.
				const formTarget = document.getElementById( 'resource-filter-summary' );

				if ( formTarget ) {
					formTarget.scrollIntoView( { behavior: 'smooth', block: 'start' } );
					formTarget.focus();

					setTimeout(
						function () {
								const firstH2 = formTarget.querySelector('h2');

								if (firstH2) {
									firstH2.focus();
								} else {
									formTarget.focus();
								}
						}
					, 100 );
				}
			}
		);

		// Handle removing individual filters from the "Filters Used" section.
		$( document ).on(
			'click',
			'.remove-filter',
			function (e) {
				e.preventDefault();

				let $filter     = $( this ).closest( '.filter-item' );
				let filterType  = $filter.data( 'type' );
				let filterValue = $filter.data( 'value' );

				// Remove the corresponding filter
				if (filterType === 'search') {
					$( '#search' ).val( '' );
				} else {
					// Dynamically handle taxonomy filters
					$( `input[name = "${filterType}[]"]:checked` ).each(
						function () {
							if ($( this ).val() === filterValue) { // Match the slug, not the name
								$( this ).prop( 'checked', false );
							}
						}
					);
				}

				// Re-trigger filtering after removing the filter
				triggerFiltering( 1 );
			}
		);
	}
);

document.addEventListener(
	'DOMContentLoaded',
	function () {
		//========== Functionality Variables ==========

		// track current open dropdown
		let currentlyOpenDropdown = null;

		//========== Helper Functions ==========

		/**
		 * Open the dropdown and close any currently open dropdown
		 * 
		 * Note: Adds a global event listener for keydown to handle
		 * tabbing and escaping. This is removed by the closDropdown
		 * function when the dropdown is closed to prevent memory leaks.
		 * 
		 * @param {HTMLElement} dropdown 
		 */
		function openDropdown(dropdown) {
			closeDropdown(); // Close any currently open dropdown
			dropdown.classList.add('open');
			dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'true');
			currentlyOpenDropdown = dropdown;
			document.addEventListener('keydown', (event) => handleKeyDown(event, dropdown));
		}

		/**
		 * Close the currently open dropdown, if any.
		 * 
		 * Note: Remove the global event listener for keydown
		 * to prevent memory leaks.
		 * @param {HTMLElment} dropdown 
		 */
		function closeDropdown(dropdown) {
			if (currentlyOpenDropdown) {
				currentlyOpenDropdown.classList.remove('open');
				currentlyOpenDropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
				currentlyOpenDropdown = null;

				document.removeEventListener('keydown', (event) => handleKeyDown(event, dropdown));
			}
		}

		/**
		 * Close the dropdown when tabbing away, we make following
		 * considerations:
		 * - If user is focused on the last element inside the dropdown,
		 *  the dropdown will close when tabbing away.
		 * - If user is focused on the first element inside the dropdown,
		 *  the dropdown will close when tabbing away (using shift key).
		 * 
		 * We refer ally-collective for preffered solution:
		 * @see https://www.a11y-collective.com/blog/mastering-web-accessibility-making-drop-down-menus-user-friendly/
		 * 	Add awarness of when a user tabs out of the menu part 
		 */
		function handleKeyDown(event, dropdown) {
			// handle tabbing
			if (event.key === 'Tab') {
				const currentFocusedElement = document.activeElement;
				const inputCollection = dropdown.querySelectorAll('input');

				const firstFocusableElement = inputCollection[0];
				const lastFocusableElement = inputCollection[inputCollection.length - 1];

				if (!event.shiftKey && currentFocusedElement === lastFocusableElement) {
					// if tabbing forward ⏩ and focused on the last element, close the dropdown
					dropdown.classList.remove('open');
					dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
				} else if (event.shiftKey && currentFocusedElement === firstFocusableElement) {
					// if tabbing backward ⏪ and focused on the first element, close the dropdown
					dropdown.classList.remove('open');
					dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
				}
			} 
			// handle escaping 
			else if (event.key === 'Escape') {
				// close dropdowns when pressing the Escape key
				dropdown.classList.remove('open');
				dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
			}
		}

		//========== Event Listeners ==========

		// toggle dropdown visibility
		document.querySelectorAll( '.custom-dropdown .dropdown-toggle' ).forEach(
			function (button) {
				button.addEventListener(
					'click',
					function () {
						const dropdown = this.parentElement;
						const isOpen = dropdown.classList.contains( 'open' );

						if (isOpen) {
							closeDropdown(dropdown);
						} else {
							openDropdown(dropdown);
						}

						// Close all other dropdowns and update aria-expanded
						// document.querySelectorAll( '.custom-dropdown' ).forEach(
						// 	function (otherDropdown) {
						// 		if (otherDropdown !== dropdown) {
						// 				otherDropdown.classList.remove( 'open' );
						// 				otherDropdown.querySelector( '.dropdown-toggle' ).setAttribute( 'aria-expanded', 'false' );
						// 		}
						// 	}
						// );

						// Toggle the current dropdown and update aria-expanded
						// const isOpen = dropdown.classList.toggle( 'open' );
						// this.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
					}
				);

				/**
				 * This will close the dropdown prematurely if the user clicks 
				 * inside the dropdown.
				 * 
				 * Note that `dropdown-menu` is absolute positioned,
				 * so it will be removed from the document flow.
				 */
				// Close dropdown when tabbing away
				// button.parentElement.addEventListener(
				// 	'focusout',
				// 	function (event) {
				// 		const dropdown      = this;
				// 		const relatedTarget = event.relatedTarget;

				// 		// Check if the newly focused element is outside the dropdown
				// 		if ( ! dropdown.contains( relatedTarget )) {
				// 			dropdown.classList.remove( 'open' );
				// 			dropdown.querySelector( '.dropdown-toggle' ).setAttribute( 'aria-expanded', 'false' );
				// 		}
				// 	}
				// );
			}
		);

		// Close dropdowns when clicking outside
		document.addEventListener(
			'click',
			function (event) {
				if ( ! event.target.closest( '.custom-dropdown' )) {
					document.querySelectorAll( '.custom-dropdown' ).forEach(
						function (dropdown) {
							dropdown.classList.remove( 'open' );
							dropdown.querySelector( '.dropdown-toggle' ).setAttribute( 'aria-expanded', 'false' );
						}
					);
				}
			}
		);

		// Close dropdowns when pressing the Escape key
		document.addEventListener(
			'keyup',
			function (event) {
				if (event.key === 'Escape') {
					document.querySelectorAll( '.custom-dropdown' ).forEach(
						function (dropdown) {
							dropdown.classList.remove( 'open' );
							dropdown.querySelector( '.dropdown-toggle' ).setAttribute( 'aria-expanded', 'false' );
						}
					);
				}
			}
		);
	}
);
