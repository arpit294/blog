<?php
require_once dirname(__DIR__) . '/database.php';


class category
{
    private $conn;
    private $table = "category";

    public function __construct()
    {
        $db = new database();
        $this->conn = $db->conn;
    }

 public function create($data)
{
    $name   = trim($data['name'] ?? '');
    $slug   = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $status = strtolower(trim($data['status'] ?? 'pending'));

    $errors = [];
    if (empty($name)) {
        $errors['name'] = "Category name is required.";
    }
    if (!in_array($status, ['pending', 'launch'], true)) {
        $errors['status'] = "Please select a valid category status";
    }
    if (!empty($errors)) {
        return ["success" => false, "errors" => $errors];
    }

    // Check for existing category (escaped input to avoid query break)
    $name_check_esc = $this->conn->real_escape_string($name);
    $check_sql = "SELECT id FROM {$this->table} WHERE name = '$name_check_esc'";
    $check_result = $this->conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        return [
            "success" => false,
            "errors" => ["name" => "Category name already exists."]
        ];
    }

    $name_esc = $this->conn->real_escape_string($name);
    $slug_esc = $this->conn->real_escape_string($slug);
    $status_esc = $this->conn->real_escape_string($status);

    $sql = "INSERT INTO category (name, slug, status) VALUES ('$name_esc', '$slug_esc', '$status_esc')";

    if ($this->conn->query($sql)) {
        $newId = $this->conn->insert_id;
        $newCategory = $this->getById($newId);
        return [
            "success" => true,
            "data" => $newCategory
        ];
    } else {
        return [
            "success" => false,
            "errors" => ["general" => "Insert failed: " . $this->conn->error]
        ];
    }
}

    public function getAll()
    {
        $categories = [];
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $result = $this->conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Ensure status has a default value
                if (empty($row['status']) || !in_array(strtolower($row['status']), ['pending', 'launch'], true)) {
                    $row['status'] = 'pending';
                } else {
                    $row['status'] = strtolower($row['status']);
                }
                $categories[] = $row;
            }
        }

        return $categories;
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM category WHERE id = $id LIMIT 1";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Ensure status has a default value
            if (empty($row['status']) || !in_array(strtolower($row['status']), ['pending', 'launch'], true)) {
                $row['status'] = 'pending';
            } else {
                $row['status'] = strtolower($row['status']);
            }
            return $row;
        }

        return null;
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $category = $this->getById($id);

        if (!$category) {
            return [
                "success" => false,
                "errors" => ["general" => "Category does not exist"]
            ];
        }

        $name = trim($data['name'] ?? '');
        $status = strtolower(trim($data['status'] ?? 'pending'));
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $errors = [];
        if (empty($name)) {
            $errors['name'] = "Please enter category name";
        }
        if (!in_array($status, ['pending', 'launch'], true)) {
            $errors['status'] = "Please select a valid category status";
        }
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        $name = $this->conn->real_escape_string($name);
        $slug = $this->conn->real_escape_string($slug);
        $status = $this->conn->real_escape_string($status);

        $sql = "UPDATE {$this->table} SET name = '$name', slug = '$slug', status = '$status' WHERE id = $id";

        if ($this->conn->query($sql)) {
            $updatedCategory = $this->getById($id);
            return [
                "success" => true,
                "data" => $updatedCategory
            ];
        } else {
            return [
                "success" => false,
                "errors" => ["general" => "Update failed: " . $this->conn->error]
            ];
        }
    }

    public function delete($id)
    {
        $id = (int)$id;
        $category = $this->getById($id);

        if (!$category) {
            return "Category does not exist.";
        }

        // Check if category has associated posts
        $cat_name = $this->conn->real_escape_string($category['name']);
        $cat_slug = $this->conn->real_escape_string($category['slug']);
        $post_check_sql = "SELECT COUNT(*) as post_count FROM blog WHERE category = '$cat_name' OR category = '$cat_slug'";
        $post_result = $this->conn->query($post_check_sql);
        $post_count = 0;
        if ($post_result && $post_result->num_rows > 0) {
            $post_row = $post_result->fetch_assoc();
            $post_count = (int)$post_row['post_count'];
        }

        if ($post_count > 0) {
            return "Cannot delete category '{$category['name']}'. It is used in {$post_count} post(s).";
        }

        $sql = "DELETE FROM {$this->table} WHERE id = $id";

        if ($this->conn->query($sql)) {
            return "Category deleted successfully.";
        } else {
            return "Delete failed: " . $this->conn->error;
        }
    }
}

