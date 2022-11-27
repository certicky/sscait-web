function login()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "login_submit.php",		
		data: $('#loginForm').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(!(msg.status))
			{
				error(1,msg.txt);
			}
			else location.replace(msg.txt);
			
			hideshow('loading',0);
		}
	});

}

function register()
{
	hideshow('loading',1);
	error(0);
	var formData = new FormData($('#regForm')[0]);
	$.ajax({
		type: "POST",
		url: "reg_submit.php",		
		data: formData,
		async: false,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		timeout: 100000,
		success: function(msg){
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			} else {
				alert("else "+msg.status);
			}
			hideshow('loading',0);
		}
	});
}

function contactus()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "contact_submit.php",		
		data: $('#contactForm').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}

function passreset()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "pass_reset_submit.php",		
		data: $('#passreset').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}

function editprofile()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "edit_profile_submit.php",		
		data: $('#editprofileForm').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}

function uploadBinary()
{
	hideshow('loading2',1);
	error2(0);
	
	var formData = new FormData($('#binaryForm')[0]);

	$.ajax({
		type: "POST",
		url: "new_binary_submit.php",		
		data: formData,
		async: false,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		//error: function(a,b,c) {alert(b);},
		success: function(msg){
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('#binaryForm').fadeOut('slow');					
				$('#ai-folder').fadeOut('slow');					
				$('#disabledError').fadeOut('slow');					
				//show the success message
				$('#binaryUploadSuccessfull').fadeIn('slow');
			}
			else
			{
				error2(1,msg.txt);
			}
			hideshow('loading2',0);
		}
	});

}

function uploadAdditional()
{
	hideshow('loading3',1);
	error3(0);
	
	var formData = new FormData($('#additionalForm')[0]);
	$.ajax({
		type: "POST",
		url: "new_additional_files_submit.php",		
		data: formData,
		async: false,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		//error: function(a,b,c) {alert(b);},
		success: function(msg){
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('#additionalForm').fadeOut('slow');					
				$('#read-folder').fadeOut('slow');					
				//show the success message
				$('#additionalUploadSuccessfull').fadeIn('slow');
			}
			else
			{
				error3(1,msg.txt);
			}
			hideshow('loading3',0);
		}
	});

}

function edituser()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "edit_user_submit.php",		
		data: $('#edituserForm').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}

function updatepass()
{
	hideshow('loading',1);
	error(0);
	var formData = new FormData($('#updatepassForm')[0]);
	console.log(formData);
	$.ajax({
		type: "POST",
		url: "change_pass_submit.php",		
		data: formData,
		async: false,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		timeout: 100000,
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}



function updateuserpass()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "change_user_pass_submit.php",		
		data: $('#updatepassForm').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}

function editsiteset()
{
	hideshow('loading',1);
	error(0);
	
	$.ajax({
		type: "POST",
		url: "edit_siteset_submit.php",		
		data: $('#sitesetForm').serialize(),
		dataType: "json",
		success: function(msg){
			
			if(parseInt(msg.status)==1)
			{
				//hide the form
				$('.form').fadeOut('slow');					
					
				//show the success message
				$('.done').fadeIn('slow');
			}
			else if(parseInt(msg.status)==0)
			{
				error(1,msg.txt);
			}
			
			hideshow('loading',0);
		}
	});

}

function hideshow(el,act)
{
	if(act) $('#'+el).css('visibility','visible');
	else $('#'+el).css('visibility','hidden');
}

function error(act,txt)
{
	hideshow('error',act);
	if(txt) $('#error').html(txt);
}

function error2(act,txt)
{
	hideshow('error2',act);
	if(txt) $('#error2').html(txt);
}

function error3(act,txt)
{
	hideshow('error3',act);
	if(txt) $('#error3').html(txt);
}


