/**
 * Autor:    Ricardo Rodriguez
 * Métodos de atualização de informações de grupos de usuários.
 */

$( function() {
	jsGroup = {};
	
	jsGroup.hasGroupName = function (groupName,divPosicao){
		if (groupName) return true;
		
		imgError = inicioImgHtmlTag +  closing  
				   + " title='"	
				   + global.error.groupEmpty + "'" 
				   + fimImgHtmlTag;
	    $(divPosicao).append(imgError);
		
	    return false;	
	}
	
	jsGroup.hasMembers = function(){
		if (!$("#groupMembers")) return true;	
		
		global.msgbox.data('messageBox').danger(window.important, 
												global.error.groupMemberNotFound);
		return false;
	}
	
	jsGroup.saveNewGroup = function(){
		var groupName  = $("#groupName").val();
		var divPosicao = '#imgGroupName';
		var users	   = $("#groupMembers").val();
		var myData     = {'groupName' : groupName,
					      'users'	  : users 	};
		var pageurl    = $("saveNewGroupPath").val();
		
		/* Check if fields is ok! */
		if (! jsGroup.hasGroupName(groupName, divPosicao)) return;
		if (! jsGroup.hasMembers) return;
		
	    $.ajax({
			
			url: pageurl,
			data: myData,
			type: 'POST',
			cache: true,
	
			beforeSend: function( ) {
			},
		
			error: function(){
			},

			success: function(returned){ 
				//debugger;
				var dataout = $.parseJSON(returned);
				if($.trim(dataout.result) === global.recordNotFound){
					
				} else  
					
				return;
			},
			statusCode: {
				404: function() {
					global.msgbox.data('messageBox').danger(window.important, 
							global.error.connection + pageurl + ". "+ global.error.tryagain);
				}
			}
		});
		
	}
	
	jsGroup.checkGroupName = function(){
		
		var groupName= $("#groupName").val();
		var pageurl  = $('#checkGroupNamePath').val();
		var divPosicao = '#imgGroupName';
		var myData = {'groupName' : groupName};
		
		if (! jsGroup.hasGroupName(groupName, divPosicao)) return; 
	
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
				 			 +	global.error.groupNameFound + "'" 
							 + fimImgHtmlTag;
				
				var dataout = $.parseJSON(returned);
				if($.trim(dataout.result) === global.recordNotFound){
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
		
	jsGroup.loadAllUsers = function() {
		/**
		 * Execute call to load all users
		 */
		window.ajaxLoading("show");
		var urlLoadAllUsers = $("#loadAllUsersPath").val();
		$.ajax({
			url:  urlLoadAllUsers,
			data: [],
			type: 'POST',
			cache: true,
			
			error: function(){
				window.ajaxLoading("hide");
				
			},
			
			success: function(returned){ 
				window.ajaxLoading("hide");
				var dataout = $.parseJSON(returned);
				if(global.usersFound  ==$.trim(dataout.result)){
					$('#groupMembers').magicsearch({
			            dataSource: dataout.users,
			            fields: ['realName', 'nickname'],
			            id: 'id',
			            format: '%realName% · %nickname%',
			            multiple: true,
			            multiField: 'realName',
			            multiStyle: {
			                space: 5,
			                width: 80
			            }
					});
					return;
				}
			},
			statusCode: {
				404: function() {
					window.ajaxLoading("hide");
					global.msgbox.data('messageBox').danger(window.important, 
							global.error.connection + pageurl + ". "+ global.error.tryagain);
				}
			},
			
		});
	
	};
	
	/**
	 * If exists $("#newGroupLoadDiv") - I'm in Group page then load all users.
	 */
	if ($("#newGroupLoadDiv").length)
		jsGroup.loadAllUsers();
	
	
});	