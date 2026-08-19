(function () {
	'use strict';

	var STORAGE_KEY = 'futbol7_owned_v1';
	var POLL_MS = 5000;
	var root = futbol7Config.root;
	var nonce = futbol7Config.nonce;
	var container = document.getElementById( 'futbol7-app' );
	if ( ! container ) {
		return;
	}

	function getOwned() {
		try {
			return JSON.parse( localStorage.getItem( STORAGE_KEY ) || '{}' );
		} catch ( e ) {
			return {};
		}
	}

	function saveOwned( owned ) {
		localStorage.setItem( STORAGE_KEY, JSON.stringify( owned ) );
	}

	function rememberOwn( id, token ) {
		var owned = getOwned();
		owned[ id ] = token;
		saveOwned( owned );
	}

	function forgetOwn( id ) {
		var owned = getOwned();
		delete owned[ id ];
		saveOwned( owned );
	}

	function api( path, options ) {
		options = options || {};
		options.headers = options.headers || {};
		options.headers[ 'X-WP-Nonce' ] = nonce;
		if ( options.body && ! ( options.body instanceof FormData ) ) {
			options.headers[ 'Content-Type' ] = 'application/json';
			options.body = JSON.stringify( options.body );
		}
		return fetch( root + path, options ).then( function ( res ) {
			return res.json().then( function ( data ) {
				if ( ! res.ok ) {
					var err = new Error( ( data && data.message ) || 'Error' );
					throw err;
				}
				return data;
			} );
		} );
	}

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str;
		return div.innerHTML;
	}

	function render( state ) {
		var owned = getOwned();
		var players = state.players;
		var byId = {};
		players.forEach( function ( p ) {
			byId[ p.id ] = p;
		} );

		var dateLabel = state.date ? state.date : 'Sin definir';
		var timeLabel = state.time ? state.time : '';

		var html = '';

		html += '<div class="futbol7-header">';
		html += '<div class="futbol7-match-info">';
		html += '<span class="futbol7-match-date">' + escapeHtml( dateLabel ) + ( timeLabel ? ' &middot; ' + escapeHtml( timeLabel ) : '' ) + '</span>';
		if ( state.notes ) {
			html += '<span class="futbol7-notes">' + escapeHtml( state.notes ) + '</span>';
		}
		html += '</div>';
		html += '<div class="futbol7-count">' + players.length + ' / ' + state.max + ' apuntados</div>';
		html += '</div>';

		if ( state.isAdmin ) {
			html += '<form class="futbol7-admin-form" id="futbol7-admin-form">';
			html += '<h3>Panel de administrador</h3>';
			html += '<label>Fecha <input type="date" name="date" value="' + escapeHtml( state.date || '' ) + '"></label>';
			html += '<label>Hora <input type="time" name="time" value="' + escapeHtml( state.time || '' ) + '"></label>';
			html += '<label class="futbol7-notes-label">Notas <input type="text" name="notes" value="' + escapeHtml( state.notes || '' ) + '" maxlength="140" placeholder="Lugar, precio, etc."></label>';
			html += '<button type="submit">Guardar</button>';
			html += '<span class="futbol7-save-msg" id="futbol7-save-msg"></span>';
			html += '</form>';
		}

		html += '<ul class="futbol7-list">';
		for ( var i = 0; i < state.max; i++ ) {
			var p = players[ i ];
			html += '<li class="futbol7-slot">';
			html += '<span class="futbol7-slot-num">' + ( i + 1 ) + '</span>';
			if ( p ) {
				var canDelete = state.isAdmin || Object.prototype.hasOwnProperty.call( owned, p.id );
				html += '<span class="futbol7-slot-name">' + escapeHtml( p.name ) + '</span>';
				if ( canDelete ) {
					html += '<button class="futbol7-remove" data-id="' + p.id + '" type="button">Borrar</button>';
				}
			} else {
				html += '<span class="futbol7-slot-empty">Libre</span>';
			}
			html += '</li>';
		}
		html += '</ul>';

		if ( players.length < state.max ) {
			html += '<form class="futbol7-join-form" id="futbol7-join-form">';
			html += '<input type="text" name="name" maxlength="60" placeholder="Tu nombre" required>';
			html += '<button type="submit">Apuntarme</button>';
			html += '</form>';
		} else {
			html += '<p class="futbol7-full">La lista esta completa.</p>';
		}

		html += '<p class="futbol7-error" id="futbol7-error"></p>';

		container.innerHTML = html;
		bindEvents();
	}

	function showError( msg ) {
		var el = document.getElementById( 'futbol7-error' );
		if ( el ) {
			el.textContent = msg;
		}
	}

	function bindEvents() {
		var joinForm = document.getElementById( 'futbol7-join-form' );
		if ( joinForm ) {
			joinForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var name = joinForm.elements.name.value.trim();
				if ( ! name ) {
					return;
				}
				api( '/players', { method: 'POST', body: { name: name } } )
					.then( function ( data ) {
						rememberOwn( data.id, data.owner_token );
						return load();
					} )
					.catch( function ( err ) {
						showError( err.message );
					} );
			} );
		}

		var adminForm = document.getElementById( 'futbol7-admin-form' );
		if ( adminForm ) {
			adminForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var body = {
					date: adminForm.elements.date.value,
					time: adminForm.elements.time.value,
					notes: adminForm.elements.notes.value,
				};
				api( '/settings', { method: 'POST', body: body } )
					.then( function () {
						var msg = document.getElementById( 'futbol7-save-msg' );
						if ( msg ) {
							msg.textContent = 'Guardado.';
							setTimeout( function () {
								msg.textContent = '';
							}, 2000 );
						}
					} )
					.catch( function ( err ) {
						showError( err.message );
					} );
			} );
		}

		var removeButtons = container.querySelectorAll( '.futbol7-remove' );
		removeButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-id' );
				var owned = getOwned();
				var body = { owner_token: owned[ id ] || '' };
				api( '/players/' + id, { method: 'DELETE', body: body } )
					.then( function () {
						forgetOwn( id );
						return load();
					} )
					.catch( function ( err ) {
						showError( err.message );
					} );
			} );
		} );
	}

	function capturePendingInput() {
		var joinForm = document.getElementById( 'futbol7-join-form' );
		if ( joinForm && document.activeElement === joinForm.elements.name && joinForm.elements.name.value ) {
			return joinForm.elements.name.value;
		}
		return null;
	}

	function load() {
		var pendingName = capturePendingInput();
		return api( '/state', { method: 'GET' } ).then( function ( state ) {
			nonce = state.nonce || nonce;
			render( state );
			if ( pendingName ) {
				var joinForm = document.getElementById( 'futbol7-join-form' );
				if ( joinForm ) {
					joinForm.elements.name.value = pendingName;
				}
			}
		} );
	}

	load();
	setInterval( load, POLL_MS );
})();
