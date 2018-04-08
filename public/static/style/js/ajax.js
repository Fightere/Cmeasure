$(document).ready(function(){
	logIn();
})
function logIn()
{
	$('#submit').bind("click",function(){
		$.post("login",{
			username:$("#username").val(),
			password:$("#password").val()
			},
			function (data){
				if (data==1) {
					window.location.href="../Index/index";
				}
				else
				{
					$("#backnews").text('密码错误!!!')
				}
			}
		)
	})
}