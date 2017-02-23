/**
 * Autor:    Ricardo Rodriguez
 * Objetivo: Métodos de atualização de informações de usuário.
 */

$( function() {
	jsProfile = {};
	jsProfile.emailFound = false;
	
	jsProfile.checkEmail = function() {
		var email 	   = $("#email").val();
		var divPosicao = '#imgEmail';
		var url        = $('#checkEmailPath').val();

		//debugger;

		//========================================================
		var	pageurl = url;			// Executa atrav�s de AJAX a p�gina informada
		//========================================================
		//	Para consultar mais opcoes possiveis numa chamada ajax
		// 		http://api.jquery.com/jQuery.ajax/
		//=========================================================
		var myData = {'email' : email};
		
		$.ajax({
			url: pageurl,
			data: myData,
			type: 'POST',
			cache: true,
	
			beforeSend: function( ) {
				$(divPosicao).empty();
				$(divPosicao).append(imgLoading);
			},
		
			error: function(){
				imgError = inicioImgHtmlTag +  closing  +
						  + " alt='"	
						  + global.error.ln001 + "'" + fimImgHtmlTag;
			    $(divPosicao).empty();
			    $(divPosicao).append(imgError);

			},

			success: function(returned){ 
				//debugger;
				$(divPosicao).empty();
				imgError = inicioImgHtmlTag +  closing  
				 			 + " title='"	
				 			 +	global.error.emailFound + "'" 
							 + fimImgHtmlTag;
				
				var dataout = $.parseJSON(returned);
				jsProfile.emailFound = false;
				if($.trim(dataout.result) === global.recordFound){
					jsProfile.emailFound = true;
					$(divPosicao).append(imgOk);
				} else  
					$(divPosicao).append(imgError);
				 
				return;
			},
			statusCode: {
				404: function() {
					global.msgbox.data('messageBox').danger(window.important, 
							global.error.connection + pageurl + ". "+ global.error.tryagain);
				}
			}
		});
	};		
	
	jsProfile.checkFields= function() {
		
		if (window.doCheckIsEmptyField("name", global.error.nameFormat)){
        	$("#name").focus();
        	return false;
        } else if (window.doCheckIsEmptyField("shortName", global.error.shortNameFormat)){
        	$("#shortName").focus();
        	return false;
        } else if (! window.docheckEmail(jsProfile.screenData.email,
        								 jsProfile.screenData.confirmEmail) ){
			$("#email").focus();
			return false;
		} else if (! pass.doVerifyPassword(jsProfile.screenData.password,
										   jsProfile.screenData.confirmPassword)){
			$("#password").focus();
			return false;
		} else if (!jsProfile.screenData.agree) {
			global.msgbox.data('messageBox').danger(window.important, global.error.confirmTerm);
			return false;
		}
		return true;
	};
	
	jsProfile.saveNewUser = function() {
		jsProfile.checkEmail();
		jsProfile.screenData = { 'name' : $("#name").val(),
								 'shortName' : $("#shortName").val(),
								 'password': 	$("#password").val(),			
								 'confirmPassword': $("#confirmPassword").val(),
								 'email' : $("#email").val(),
								 'confirmEmail': $("#confirmEmail").val(),
								 'agree': $('#agree').is(":checked"),
								 'path' : $("#newPath").val(),
								 'login': $("#loginPath").val(),
				     			};
		if  (! jsProfile.checkFields())
			return false;
		
		/**
		 * Execute the call of save record
		 */
		
		$.ajax({
			url: jsProfile.screenData.path,
			data: jsProfile.screenData,
			type: 'POST',
			cache: false,
				
			success: function(returned){ 
				//debugger;
				$('#loadingDiv').hide();
				var dataout = $.parseJSON(returned);
				if($.trim(dataout.result) === global.recordSavedWithSuccess){
					location.href =jsProfile.screenData.login;
				} else  
					alert('Erro no cadastro:');
				return;
			},
			statusCode: {
				404: function() {
					global.msgbox.data('messageBox').danger(window.important, 
							global.error.connection + pageurl + ". "+ global.error.tryagain);
				}
			}
		});
	};		
	
});
