<?php
// classes/Application.php (Updated)
/**
 * Application class to handle creation of application records
 * New fields: name, age, address, mobile, fund_purpose, story, urgent, id_card, photo, proof
 */
class Application {
    private $db;

    /**
     * Constructor: accepts Database instance
     *
     * @param Database $database
     */
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Create a new application record (Updated for new structure)
     *
     * @param array $data associative array containing:
     *                   - name: applicant name (optional)
     *                   - age: applicant age (optional)
     *                   - address: applicant address (optional)
     *                   - mobile: applicant mobile number (required)
     *                   - fund_purpose: purpose of fund (optional)
     *                   - story: story of applicant (optional)
     *                   - urgent: urgent flag (default 0)
     *                   - id_card: ID card file name (optional)
     *                   - photo: photo file name (optional)
     *                   - proof: proof file name (optional)
     * @return PDOStatement on success
     */
    public function create($data) {
        $sql = "INSERT INTO applications (name, age, address, mobile, fund_purpose, story, urgent, id_card, photo, proof, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        return $this->db->query($sql, [
            $data['name'] ?? null,
            $data['age'] ?? null,
            $data['address'] ?? null,
            $data['mobile'],
            $data['fund_purpose'] ?? null,
            $data['story'] ?? null,
            $data['urgent'] ?? 0,
            $data['id_card'] ?? null,
            $data['photo'] ?? null,
            $data['proof'] ?? null
        ]);
    }
}