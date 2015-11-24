<?
//Herman Auth - A simple authentication script for the Pooch framework
//Written By Kevin Kaske

//-----------------------------------------------------------------------------------
// Login / Authentication Functions
//-----------------------------------------------------------------------------------
if(isset($config['herman_start_session']) && !$config['herman_start_session'])){
	//do nothing... Config says to not start the session
}else{
	//else default to starting the session
	session_start();
}

Class HermanAuth {
	public $db;

	public function __construct(){
		global $config;
		$db = new MysqliDb ($config['db_host'], $config['db_username'], $config['db_password'], $config['db_database']);
		$this->db = $db;
	}

	public function validateUser($email){
		session_regenerate_id(); //this is a security measure
		$_SESSION['valid']   = 1;
		$_SESSION['user_id'] = "";
		$_SESSION['account_id'] = "";
		$_SESSION['control_number'] = "";
		$_SESSION['email']   = "";

		try{
			$this->db->where('email', $email);
			$rows = $this->db->get('users');
			$userData = null;

			if(count($rows) > 0){
				$userData = $rows[0];
			}else{
				die("An error has occurred. Please try again later.");
			}

			$_SESSION['user_id']        = $userData['id'];
			$_SESSION['email']          = $userData['email'];
			$_SESSION['account_id']     = $userData['account_id'];

			if($userData['admin'] == 1){
				$_SESSION['admin'] = true;
			}else{
				$_SESSION['admin'] = false;
			}

		}catch (MyException $e){
			die("An error has occurred. Please try again later.");
		}
	}

	public function isLoggedIn(){
		if(isset($_SESSION['valid'])){
			return true;
		}else{
			return false;
		}
	}

	public function isAdmin(){
		if(isset($_SESSION['admin']) && $_SESSION['admin'] == true){
			return true;
		}else{
			return false;
		}
	}

	public function isMaster(){
		if(isset($_SESSION['is_master']) && $_SESSION['is_master'] == true){
			return true;
		}else{
			return false;
		}
	}

	public function logUserIn($email, $password, $remember=false){
		global $config;

		try{
			$this->db->where('email', $email);
			$rows = $this->db->get('users');

			$userData = null;

			if(count($rows) < 1){
				$this->logInvalidLogin($email);

				flash('error', 'Incorrect username or password. Try again.');
				session_write_close();
				header('Location: '.$config['address'].'/login');

				die();
			}else{
				$userData = $rows[0];
			}

			if(!password_verify($password, $userData['password'])){ //incorrect password
				$this->logInvalidLogin($email);

				flash('error', 'Incorrect username or password. Try again.');
				session_write_close();
				header('Location: '.$config['address'].'/login');

				die();
			}

			$this->validateUser($userData['email']);

			if($remember){
				$params = session_get_cookie_params();
				setcookie(session_name(), $_COOKIE[session_name()], time() + 60*60*24*30, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
			}

			header('Location: '.$config['address']);
		}catch (MyException $e){
			$this->logInvalidLogin($email);

			flash('error', 'An error has occurred. Please try again later.');
			session_write_close();
			header('Location: '.$config['address'].'/login');

			die();
		}
	}



	//--------------------------------------------
	// This is a bruteforce prevention mechanism
	//--------------------------------------------
	public function logInvalidLogin($email){
		$params = array($email);
		$this->db->rawQuery("INSERT INTO failed_logins SET email = ?, date_attempted = CURRENT_TIMESTAMP", $params);

		//if this user has had more then 10 fails in the last 15 minutes
		//start to deplay the response by 1 second for every fail over 10
		//example: 11 fails would delay 1 second
		//         12 fails would delay 2 seconds
		$params = array($email);
		$rows = $this->db->rawQuery("SELECT COUNT(1) AS failed FROM failed_logins WHERE date_attempted > DATE_SUB(NOW(), INTERVAL 15 minute) and email = ?", $params, false);

		$failedData = $rows[0];
		$failed = intval($failedData['failed']);

		if($failed > 10){

			$failed = $failed - 10; // get the number of seconds by getting a second for every fail past 10

			if($failed > 15){ // make sure we cap the number of seconds that can delay at 15 seconds
				$failed = 15;
			}

			sleep($failed);
		}
	}

	//This is used for impersonating a user for support reasons
	public function logUserInWithoutPassword($email){
		$params = array($email);
		$rows = $this->db->rawQuery("SELECT password, salt, email, is_admin, approved, agreed_to_terms FROM users WHERE email = ?", $params);

		try{
			$userData = $rows[0];

			if(!$userData){
				$this->logInvalidLogin($email);

				flash('error', 'Incorrect username or password.');
				session_write_close();

				header('Location: '.$config['address'].'/login');
				die();
			}else{
				if($userData['approved'] != true){
					if($userData['agreed_to_terms'] != true){
						flash('error', 'You will receive an email letting you know when your account has been approved.');
					}else{
						flash('error', 'Account has been disabled by admin. Please contact your sites admin for more information.');
					}
					session_write_close();

					header('Location: '.$config['address'].'/login');
					die();
				}
			}
			$this->validateUser($userData['email']);
			if($userData['agreed_to_terms'] != true){
				header('Location: '.$config['address'].'/terms');
				die();
			}
			$this->loadHome();
		}catch (MyException $e){
			flash('error', 'An error has occurred. Please try again later.');
			session_write_close();

			header('Location: '.$config['address'].'/login');
			die();
		}
	}

	public function logout(){
		$_SESSION = array(); //destroy all of the session variables
		session_destroy();
	}

	public function membersOnly(){
		global $config;
		if(!$this->isLoggedIn()){
			header('Location: '.$config['address'].'/login');
			die();
		}else{
			$this->validateUser($_SESSION['email']);
		}
	}

	public function adminOnly(){
		global $config;
		if(!$this->isLoggedIn()){
			header('Location: '.$config['address'].'/login');
			die();
		}

		if(!$this->isAdmin()){
			flash('error', 'This section of the site requires<br />you to be an administrator.');
			session_write_close();

			header('Location: '.$config['address'].'/login');
			die();
		}else{
			$this->validateUser($_SESSION['email']);
		}
	}
}
?>
