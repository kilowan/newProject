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
		public string $name = "";
		public string $surname1 = "";
		public ?string $surname2 = "";
		public string $dni = "";
		public string $tipo = "";
		public ?int $id = NULL;
		public $permissions = null;
		public int $borrado = 0;
	}
	class credentials
	{
		public string $username;
		public string $password;
		public ?int $employee;
		
		public function __construct(string $username, string $password, ?int $employee = null)
		{
			$this->username = $username;
			$this->password = MD5($password);
			$this->employee = $employee;
		}
	}
	class note
	{
		public string $noteStr = "";
		public $date = null;
	}

    class incidence
    {
        public $owner = null;
        public $solver = null;
        public $initDateTime = null;
        public $finishTime = null;
        public $finishDate = null;
        public string $issueDesc = "";
        public $pieces = null;
		public ?array $notes = null;
		public int $state = 1;
		public ?int $id = null;
		
		public function __construct($owner, $initDateTime, string $issueDesc, $pieces, ?array $notes)
		{
			$this->owner = $owner;
			$this->initDateTime = $initDateTime;
			$this->issueDesc = $issueDesc;
			$this->pieces = $pieces;
			$this->notes = $notes;
		}
	}
	class piece
	{
		public ?int $id = null;
		public string $name = "";
		public $price = 0;
		public string $description = "";
		public $type = null;
	}
	class pieceType
	{
		public string $description = "";
		public string $name = "";
	}
	class statistics
	{
		public ?string $employeeName = null;
		public ?string $average = null;
		public ?int $solvedIncidences = null;
	}
	class reportedPiece
	{
		public ?string $pieceName = null;
		public ?string $pieceNumber = null;
	}
	class dictionary
	{
		public $column = '';
		public $value = '';
	}
?>