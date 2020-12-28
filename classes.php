<?php
    class sql
	{
		public string $host_db = "";
		public string $user_db = "";
		public string $pass_db = "";
		public string $db_name = "";
	}

	class user
	{
		public string $comName = "";
		public string $name = "";
		public string $surname1 = "";
		public ?string $surname2 = "";
		public string $dni = "";
		public string $tipo = "";
		public ?int $id = NULL;
		//public string $username = "";
		//public string $password = "";
	}
	class credentials
	{
		public string $username;
		public string $password;
		public function __construct(string $username, string $password)
		{
			$this->username = $username;
			$this->password = MD5($password);
		}
	}

    /*class incidence
    {
        public $owner = new user;
        public $solver = new user;
        public $initDateTime = null;
        public $finishTime = null;
        public $finishDate = null;
        public string $issueDesc = "";
        public string $piece = "";
        public array $notes = null;
    }*/
?>