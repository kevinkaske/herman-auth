<?
class SessionsController extends ApplicationController{
	function login(){
		//Show Login Page
		$values = array();
		$values['csrf_meta'] = $this->auth->getCSRFMeta();

		$this->values = $values;
	}

	function process(){
		if(isset($_POST['email']) && isset($_POST['password'])){
			$this->auth->logUserIn($_POST['email'], $_POST['password'], isset($_POST['remember']), $_POST['csrf_token']);
		}else{
			flash('error', 'Incorrect username or password. Try again.');
			session_write_close();
			$this->redirect($this->config['address'].'/login');
		}
	}

	function logout(){
		$this->auth->logout();

		$this->redirect($this->config['root']);
	}
}
?>
