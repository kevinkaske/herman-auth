<?
class SessionsController extends ApplicationController{
  function login(){
    //Show Login Page
	}

	function process(){
		$this->auth->logUserIn($_POST['email'], $_POST['password']);
	}

	function logout(){
		$this->auth->logout();

		$this->redirect($this->config['root']);
	}
}
?>
