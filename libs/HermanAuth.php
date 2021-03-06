<?
//Herman Auth - A simple authentication script for the Pooch framework
//Written By Kevin Kaske

//-----------------------------------------------------------------------------------
// Login / Authentication Functions
//-----------------------------------------------------------------------------------
Class HermanAuth {
	public $db;
	private $tokenAuthEnabled;
	private $newUsersAgreeToTermsEnabled;
	private $newUsersTermsPage;
	private $newUsersNeedAprovalEnabled;
	private $startSessions;
	private $accountIdFieldName;
	private $csrfProtection;
	private $csrfProtectionLength;

	public function __construct(){
		global $config;

		$db = new MysqliDb ($config['db_host'], $config['db_username'], $config['db_password'], $config['db_database']);
		$this->db = $db;

		//-----------------------------------------
		// Plugin Options
		//-----------------------------------------
		$this->tokenAuthEnabled            = false;
		$this->newUsersAgreeToTermsEnabled = false;
		$this->newUsersTermsPage           = '';
		$this->newUsersNeedAprovalEnabled  = false;
		$this->csrfProtection              = true;
		$this->csrfProtectionLength        = 20;
		$this->accountIdFieldName          = 'account_id';
		
		//-----------------------------------------
		//Set Plugin Options From Pooch Config File
		//-----------------------------------------
		if(isset($config['herman_start_session']) && $config['herman_start_session'] == false){
			//do nothing... Config says to not start the session
		}else{
			//else default to starting the session
			session_start();
		}
		
		if(isset($config['herman_new_users_agree_to_terms']) && $config['herman_new_users_agree_to_terms'] == true){
			$this->newUsersAgreeToTermsEnabled = true;
		}
		
		if(isset($config['herman_new_users_terms_page'])){
			$this->newUsersTermsPage = $config['herman_new_users_terms_page'];
		}else{
			$this->newUsersTermsPage = '/terms';
		}
		
		if(isset($config['herman_new_users_approval']) && $config['herman_new_users_approval'] == true){
			$this->newUsersNeedAprovalEnabled = true;
		}
		
		if(isset($config['herman_csrf_protection']) && $config['herman_csrf_protection'] == false){
			$this->csrfProtection = false;
		}
	}

	//-----------------------------------------
	//Functions for Setting Plugin Options
	//-----------------------------------------
	public function enableTokenAuth(){
		$this->tokenAuthEnabled = true;
	}
	
	public function enableNewUsersAgreeToTerms(){
		$this->newUsersAgreeToTermsEnabled = true;
	}
	
	public function setNewUsersTermsPage($terms_page){
		$this->newUsersTermsPage = $terms_page;
	}
	
	public function enableNewUsersApproval(){
		$this->newUsersNeedAprovalEnabled = true;
	}
	
	public function setAccountIdField($account_id_field_name){
		//Defaults to account_id. You can pass in an empty string if you
		//don't want to handle an account_id.
		$this->accountIdFieldName = $account_id_field_name;
	}
	
	public function disableCSRFProtection(){
		$this->csrfProtection = false;
	}
	//-----------------------------------------
	
	//CSRF based on Rails implimentation
	//https://medium.com/@mctaylorpants/a-deep-dive-into-csrf-protection-in-rails-19fa0a42c0ef
	public function getCSRFMeta(){
		$csrfOneTimePad = getToken($this->csrfProtectionLength);
		$_SESSION['csrf_token'] = getToken($this->csrfProtectionLength);
		$csrfXOR = $this->cipher($_SESSION['csrf_token'], $csrfOneTimePad);
		
		$masked_token = $csrfOneTimePad.$csrfXOR;
		return '<input type="hidden" name="csrf" value="'.base64_encode($masked_token).'">';
	}
	
	public function validateUser($email){
		session_regenerate_id(); //this is a security measure
		$_SESSION['valid']   = 1;
		$_SESSION['user_id'] = "";
		$_SESSION['email']   = "";
		if($this->accountIdFieldName != ''){
			$_SESSION[$this->accountIdFieldName] = "";
		}

		try{
			$this->db->where('email', $email);
			$rows = $this->db->get('users');
			$userData = null;

			if(count($rows) > 0){
				$userData = $rows[0];
			}else{
				die("An error has occurred. Please try again later.");
			}

			$_SESSION['user_id'] = $userData['id'];
			$_SESSION['email']   = $userData['email'];

			if($this->accountIdFieldName != ''){
				$_SESSION[$this->accountIdFieldName] = $userData[$this->accountIdFieldName];
			}

			if($userData['admin'] == 1){
				$_SESSION['admin'] = true;
			}else{
				$_SESSION['admin'] = false;
			}

		}catch (MyException $e){
			die("An error has occurred. Please try again later.");
		}
	}

	public function checkCSRFToken($csrf_base64_token){
		if($csrf_base64_token == null || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] == ''){
			$_SESSION['csrf_token'] = '';
			flash('error', 'An error has occurred. Try again.');
			session_write_close();
			header('Location: '.$config['address'].'/login');
			die();
		}
		$masked_token = base64_decode($csrf_base64_token);
		$unmasked_token = '';
		
		if(strlen($masked_token) == $this->csrfProtectionLength){
			$unmasked_token = $masked_token;
		}elseif(strlen($masked_token) == ($this->csrfProtectionLength * 2)){
			$unmasked_token = $this->unmaskCSRFToken($masked_token);
		}else{
			//Token is malformed
			$_SESSION['csrf_token'] = '';
			flash('error', 'An error has occurred. Try again.');
			session_write_close();
			header('Location: '.$config['address'].'/login');
			die();
		}
		
		//validate csrf token here
		if($_SESSION['csrf_token'] == $unmasked_token){
			//Token is valid now let's empty it so that it cannot be reused
			$_SESSION['csrf_token'] = '';
		}else{
			//Token is not valid so we ned to empty it and redirect
			$_SESSION['csrf_token'] = '';
			
			flash('error', 'An error has occurred. Try again.');
			session_write_close();
			header('Location: '.$config['address'].'/login');
			die();
		}
	}
	
	private function unmaskCSRFToken($masked_token){
		$one_time_pad = substr($masked_token , 0, $this->csrfProtectionLength);
		$encrypted_csrf_token = substr($masked_token , $this->csrfProtectionLength);
		return $this->plaintext($encrypted_csrf_token, $one_time_pad);
	}

	public function logUserIn($email, $password, $remember=false, $csrf_base64_token=null){
		global $config;

		
		if($this->csrfProtection){
			$this->checkCSRFToken($csrf_base64_token);
		}

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

			if($this->newUsersNeedAprovalEnabled && $userData['approved'] != true){
				
				if($this->newUsersAgreeToTermsEnabled && $userData['agreed_to_terms'] != true){
					flash('error', 'You will receive an email letting you know when your account has been approved.');
				}else{
					flash('error', 'Account has been disabled by admin. Please contact your sites admin for more information.');
				}
				session_write_close();

				header('Location: '.$config['address'].'/login');
				die();
			}

			if($this->newUsersAgreeToTermsEnabled && $userData['agreed_to_terms'] != true){
				header('Location: '.$config['address'].$this->newUsersTermsPage);
				die();
			}else{
				header('Location: '.$config['address']);
			}

			$this->validateUser($userData['email']);

			//Set "Remember Me" cookie
			if($remember){
				//Insert selector and validator into db
				$selector = getToken(12);
				$validator = getToken(15);
				$expires = time()+60*60*24*30*12;

				setcookie('validator', $selector.'-'.$validator, $expires, '/');


				$data = Array ('selector' => $selector,
												'hashedValidator' => hash('sha256', $validator),
												'user_id' => $userData['id'],
												'expires' => $this->db->now('+1Y')
				);

				$id = $this->db->insert('cookie_tokens', $data);

				//Get rid of old tokens from db
				$this->db->rawQuery("DELETE FROM cookie_tokens WHERE expires < NOW()");
			}

			session_write_close();
			
			die();
		}catch (MyException $e){
			$this->logInvalidLogin($email);

			flash('error', 'An error has occurred. Please try again later.');
			session_write_close();
			header('Location: '.$config['address'].'/login');

			die();
		}
	}



	//--------------------------------------------
	// Bruteforce prevention mechanism
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
	//be extreamly careful to protect parts of the site you might
	//call this code from!
	public function logUserInWithoutPassword($email){
		$this->validateUser($email);
		$this->loadHome();
	}

	public function logout(){
		if(isset($_COOKIE['validator'])){
			$validator_array = explode('-', $_COOKIE['validator']);
			if(count($validator_array) > 1){
				$this->db->rawQuery("DELETE FROM cookie_tokens WHERE selector = ?", array($validator_array[0]));
			}
			
			setcookie('validator', '', time()-3600);
		}
		$_SESSION = array(); //destroy all of the session variables
		session_destroy();
	}

	public function membersOnly(){
		global $config, $query_string;
		if(!$this->isLoggedIn()){
			if($this->tokenAuthEnabled && (isset($_POST['token']) || isset($query_string['token']))){
				$this->logInWithToken();
			}elseif(isset($_COOKIE['validator']) && $_COOKIE['validator'] != ''){
				$this->logInWithCookie();
			}else{
				header('Location: '.$config['address'].'/login');
				die();
			}
		}else{
			$this->validateUser($_SESSION['email']);
		}
	}

	public function logInWithCookie(){
		global $config, $query_string;
		//Based on https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence#title.2

		//1. Separate selector from validator
		$validator_array = explode('-', $_COOKIE['validator']);
		if(count($validator_array) < 2){
			header('Location: '.$config['address'].'/login');
			die();
		}

		//2. Grab the row in auth_tokens for the given selector. If none is found, abort.
		$this->db->where('selector', $validator_array[0]);
		$cookieToken = $this->db->getOne('cookie_tokens');

		if(!$cookieToken){
			header('Location: '.$config['address'].'/login');
			die();
		}

		//3. Hash the validator provided by the user's cookie with SHA-256.
		$cookie_hashed_validator = hash('sha256', $validator_array[1]);

		//4. Compare the SHA-256 hash we generated with the hash stored in the database, using hash_equals()
		if(hash_equals($cookieToken['hashedValidator'], $cookie_hashed_validator)){
			//5. If step 4 passes, associate the current session with the appropriate user ID.
			$this->db->where('id', $cookieToken['user_id']);
			$userData = $this->db->getOne('users');

			$this->validateUser($userData['email']);

			session_write_close();
			if(isset($_SERVER['REQUEST_URI'])){
				header('Location: '.$config['address'].$_SERVER['REQUEST_URI']);
			}else{
				header('Location: '.$config['address']);
			}
			die();
		}else{
			header('Location: '.$config['address'].'/login');
			die();
		}
	}

	public function logInWithToken(){
		$current_token = '';
		if(isset($_POST['token'])){
			$current_token = $_POST['token'];
		}

		if(isset($query_string['token'])){
			$current_token = $query_string['token'];
		}

		$this->db->where('token', $current_token);
		$rows = $this->db->get('users');

		$userData = null;

		if(count($rows) < 1){
			$this->logInvalidLogin($email);

			echo '{ "error": "Incorrect token" }';
			session_write_close();

			die();
		}else{
			$userData = $rows[0];
		}

		$this->validateUser($userData['email']);
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
	
	public function isLoggedIn(){
		if(isset($_SESSION['valid'])){
			return true;
		}else{
			return false;
		}
	}
	
	//Utility cipher functions for csrf
	private function cipher($plaintext, $key) {
		$key = $this->text2ascii($key);
		$plaintext = $this->text2ascii($plaintext);

		$keysize = count($key);
		$input_size = count($plaintext);

		$cipher = "";
		
		for ($i = 0; $i < $input_size; $i++)
			$cipher .= chr($plaintext[$i] ^ $key[$i % $keysize]);

		return $cipher;
	}

	private function plaintext($cipher, $key) {
		$key = $this->text2ascii($key);
		$cipher = $this->text2ascii($cipher);
		$keysize = count($key);
		$input_size = count($cipher);
		$plaintext = "";
		
		for ($i = 0; $i < $input_size; $i++)
			$plaintext .= chr($cipher[$i] ^ $key[$i % $keysize]);

		return $plaintext;
	}
	
	private function text2ascii($text) {
		return array_map('ord', str_split($text));
	}
}

//Polyfill for versions of php that are older than 5.6. Should be able to remove some day!
//Copied from http://php.net/manual/en/function.hash-equals.php#115635
if(!function_exists('hash_equals')) {
	function hash_equals($str1, $str2) {
		if(strlen($str1) != strlen($str2)) {
			return false;
		} else {
			$res = $str1 ^ $str2;
			$ret = 0;
			for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
			return !$ret;
		}
	}
}
?>
