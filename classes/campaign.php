<?php
// classes/Campaign.php
class Campaign {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function create($data) {
        $sql = "INSERT INTO campaigns (title, description, goal_amount, category_id, image, start_date, end_date, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['title'],
            $data['description'],
            $data['goal_amount'],
            $data['category_id'],
            $data['image'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['created_by']
        ]);
    }
    
    public function getCampaignsWithCategories($limit = null) {
        $sql = "SELECT c.*, 
                       cat.name as category_name, 
                       parent.name as parent_category_name,
                       parent.id as parent_category_id
                FROM campaigns c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN categories parent ON cat.parent_id = parent.id 
                WHERE c.status = 'active' 
                ORDER BY c.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCampaignsByCategory($category_id) {
        $sql = "SELECT c.*, 
                       cat.name as category_name, 
                       parent.name as parent_category_name
                FROM campaigns c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN categories parent ON cat.parent_id = parent.id 
                WHERE c.status = 'active' AND c.category_id = ? 
                ORDER BY c.created_at DESC";
        
        return $this->db->query($sql, [$category_id])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCampaignsByParentCategory($parent_category_id) {
        $sql = "SELECT c.*, 
                       cat.name as category_name, 
                       parent.name as parent_category_name
                FROM campaigns c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN categories parent ON cat.parent_id = parent.id 
                WHERE c.status = 'active' AND cat.parent_id = ? 
                ORDER BY c.created_at DESC";
        
        return $this->db->query($sql, [$parent_category_id])->fetchAll(PDO::FETCH_ASSOC);
    }
}