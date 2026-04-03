<?php
require_once dirname(__DIR__) . '/database.php';

class Post
{
    private $conn;

    public function __construct()
    {
        $db = new database();
        $this->conn = $db->conn;
    }

    private function validatePostData($data, $file)
    {
        $errors = [];

        $title = trim($data['title'] ?? '');
        $shortDescription = trim($data['short_description'] ?? '');
        $content = trim($data['content'] ?? '');
        $category = trim($data['category'] ?? '');
        $status = strtolower(trim($data['status'] ?? ''));
        $imageName = trim($file['name'] ?? '');

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }
        if ($shortDescription === '') {
            $errors['short_description'] = 'Short description is required.';
        }
        if ($content === '') {
            $errors['content'] = 'Content is required.';
        }
        if ($category === '') {
            $errors['category'] = 'Category is required.';
        }
        if (!in_array($status, ['draft', 'published', 'unpublished'], true)) {
            $errors['status'] = 'Please select a valid status.';
        }
        if ($imageName === '') {
            $errors['image'] = 'Image is required.';
        }

        return $errors;
    }
    //this is for create 
    public function create($data, $file)
    {
        $title = trim($data['title']);
        $shortDescription = trim($data['short_description']);
        $content = trim($data['content']);
        $category = trim($data['category']);
        $status = $data['status'];
        $image = "";
        $createdBy = $_SESSION['user_name'] ?? "Admin";

        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $errors = $this->validatePostData($data, $file);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        if (!empty($file['name'])) {
            // Use a truly unique filename to avoid collisions during fast updates.
            $imageName = time() . "_" . bin2hex(random_bytes(6)) . "_" . basename($file['name']);
            $uploadFolder = dirname(__DIR__, 2) . "/assets/posts/";

            move_uploaded_file($file['tmp_name'], $uploadFolder . $imageName);
            $image = "assets/posts/" . $imageName;
        }

       

        $title = $this->conn->real_escape_string($title);
        $shortDescription = $this->conn->real_escape_string($shortDescription);
        $content = $this->conn->real_escape_string($content);
        $image = $this->conn->real_escape_string($image);
        $status = $this->conn->real_escape_string($status);
        $category = $this->conn->real_escape_string($category);
        $createdBy = $this->conn->real_escape_string($createdBy);
        $slug = $this->conn->real_escape_string($slug);

        $sql = "INSERT INTO blog
            (title, content, image, status, category, createdby, `sort des`, slug)
            VALUES
            ('$title', '$content', '$image', '$status', '$category', '$createdBy', '$shortDescription', '$slug')";

        if ($this->conn->query($sql)) {
            return [
                'success' => true,
                'message' => 'Post created successfully'
            ];
        }

        return [
            'success' => false,
            'errors' => ['general' => 'Error: ' . $this->conn->error]
        ];
    }
    //this is for read
    public function getAll()
    {
        $posts = [];
        $sql = "SELECT * FROM blog ORDER BY posted DESC, id DESC";
        $result = $this->conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }

        return $posts;
    }
    //this is for counting the total number of post
    public function countAll()
    {
        $sql = "SELECT COUNT(*) AS total FROM blog";
        $result = $this->conn->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return (int) $row['total'];
        }

        return 0;
    }
    //this is for get by id for edit and delete
    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM blog WHERE id = $id LIMIT 1";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }
    //this is for update
    public function update($id, $data, $file)
    {
        $id = (int) $id;
        $post = $this->getById($id);

        if (!$post) {
            return [
                'success' => false,
                'errors' => ['general' => 'Post not found.']
            ];
        }

        $title = trim($data['title']);
        $shortDescription = trim($data['short_description']);
        $content = trim($data['content']);
        $category = trim($data['category']);
        $status = $data['status'];
        $image = $post['image'];
        $oldImageRel = $post['image'] ?? '';
        $isReplacingImage = !empty($file['name']);
        $createdBy = $_SESSION['user_name'] ?? $post['createdby'];

        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $errors = $this->validatePostData($data, $file);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        if (!empty($file['name'])) {
            // Use a truly unique filename to avoid collisions during fast updates.
            $imageName = time() . "_" . bin2hex(random_bytes(6)) . "_" . basename($file['name']);
            $uploadFolder = dirname(__DIR__, 2) . "/assets/posts/";

            if (!is_dir($uploadFolder)) {
                mkdir($uploadFolder, 0777, true);
            }

            move_uploaded_file($file['tmp_name'], $uploadFolder . $imageName);
            $image = "assets/posts/" . $imageName;
        }

        $status = ($status == "published") ? "Published" : "Unpublished";

        $title = $this->conn->real_escape_string($title);
        $shortDescription = $this->conn->real_escape_string($shortDescription);
        $content = $this->conn->real_escape_string($content);
        $image = $this->conn->real_escape_string($image);
        $status = $this->conn->real_escape_string($status);
        $category = $this->conn->real_escape_string($category);
        $createdBy = $this->conn->real_escape_string($createdBy);
        $slug = $this->conn->real_escape_string($slug);

        $sql = "UPDATE blog SET
                title = '$title',
                content = '$content',
                image = '$image',
                status = '$status',
                category = '$category',
                createdby = '$createdBy',
                `sort des` = '$shortDescription',
                slug = '$slug'
                WHERE id = $id";

        if ($this->conn->query($sql)) {
            // If we replaced the image, and there was an old image, delete the old image file.
            if ($isReplacingImage && !empty($oldImageRel)) {
                $uploadFolder = dirname(__DIR__, 2) . "/assets/posts/";
                $oldFileName = basename($oldImageRel); 
                $newFileName = basename($image); 

                // Safety: if filenames collide (or were the same file), don't delete.
                if ($oldFileName !== $newFileName) {
                    $oldPath = $uploadFolder . $oldFileName;
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Post updated successfully.'
            ];
        }

        return [
            'success' => false,
            'errors' => ['general' => 'Error: ' . $this->conn->error]
        ];
    }
    //this is for delete
    public function delete($id)
    {
        $id = (int) $id;
        $post = $this->getById($id);

        if (!$post) {
            return "Post not found.";
        }

        $sql = "DELETE FROM blog WHERE id = $id";

        if ($this->conn->query($sql)) {
            // Delete the post image from disk (if it exists).
            $imageRel = $post['image'] ?? '';
            if (!empty($imageRel)) {
                $uploadFolder = dirname(__DIR__, 2) . "/assets/posts/";
                $imageFileName = basename($imageRel); 
                $imagePath = $uploadFolder . $imageFileName;
                if (is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
            return "Post deleted successfully.";
        } else {
            return "Error: " . $this->conn->error;
        }
    }
}


