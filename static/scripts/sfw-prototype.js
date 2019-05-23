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



			}

		} );

	}
	
	return {
		init: init
	};
	
}());

Utils.ready( SFWPrototype.init );
