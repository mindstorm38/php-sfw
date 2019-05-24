const SFWPrototype = (function(){
	
	function init() {
		
		Form.addChecker( "sfw-proto-login", "user", Form.NOT_EMPTY_CHECKER );
		Form.addEnterKeyListener( "sfw-proto-login", ["user", "password"] );
		Form.setFormSubmitAction( "sfw-proto-login", login );

	}

	function login() {

		const values = Form.getFormValues("sfw-proto-login");

		Query.post( "PrototypeLogin", values, function(err, data, msg) {

			if ( err ) {

				Form.getField("sfw-proto-login", "user").classList.add("invalid");
				Form.getField("sfw-proto-login", "password").classList.add("invalid");

				setTimeout( function() {

					Form.getField("sfw-proto-login", "user").classList.remove("invalid");
					Form.getField("sfw-proto-login", "password").classList.remove("invalid");

				}, 1000 );

				return;

			}

			Utils.reloadLocation();

		} );

	}
	
	return {
		init: init
	};
	
}());

Utils.ready( SFWPrototype.init );
