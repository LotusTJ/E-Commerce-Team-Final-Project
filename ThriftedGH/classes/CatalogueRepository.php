<?php
/** Task 6: read-only public catalogue queries. No sample inventory. */
require_once __DIR__ . '/../core/db_class.php';
class CatalogueRepository {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    private function rows($sql, $types = '', $values = []) {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) { throw new RuntimeException('Catalogue query preparation failed'); }
        if ($values) { $stmt->bind_param($types, ...$values); }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    private function base() {
        return " FROM products p JOIN categories c ON c.category_id=p.category_id JOIN seller_profiles sp ON sp.seller_id=p.seller_id WHERE p.status='available' AND p.stock_quantity=1 AND EXISTS (SELECT 1 FROM seller_applications sa WHERE sa.user_id=sp.user_id AND sa.status='approved')";
    }
    public function categories() { return $this->rows('SELECT category_id, category_name FROM categories ORDER BY category_name'); }
    public function sizes() { return $this->rows('SELECT DISTINCT p.size AS value'.$this->base()." AND p.size IS NOT NULL AND p.size<>'' ORDER BY p.size"); }
    public function browse(array $f = [], $limit = 100) {
        $sql='SELECT p.product_id,p.title,p.description,p.price,p.size,p.item_condition,p.image_path,p.seller_id,c.category_name,sp.shop_name AS seller_name'.$this->base();
        $types=''; $values=[];
        if (!empty($f['q'])) { $sql.=' AND p.title LIKE ?'; $types.='s'; $values[]='%'.$f['q'].'%'; }
        if (!empty($f['category']) && ctype_digit((string)$f['category'])) { $sql.=' AND p.category_id=?'; $types.='i'; $values[]=(int)$f['category']; }
        if (!empty($f['size'])) { $sql.=' AND p.size=?'; $types.='s'; $values[]=$f['size']; }
        if (in_array($f['condition']??'', ['like_new','good','fair'],true)) { $sql.=' AND p.item_condition=?'; $types.='s'; $values[]=$f['condition']; }
        if (isset($f['min_price']) && $f['min_price']!=='' && is_numeric($f['min_price']) && (float)$f['min_price']>=0) { $sql.=' AND p.price>=?'; $types.='d'; $values[]=(float)$f['min_price']; }
        if (isset($f['max_price']) && $f['max_price']!=='' && is_numeric($f['max_price']) && (float)$f['max_price']>=0) { $sql.=' AND p.price<=?'; $types.='d'; $values[]=(float)$f['max_price']; }
        $sort=$f['sort']??'';
        $sql.= $sort==='price-asc'?' ORDER BY p.price ASC,p.product_id DESC':($sort==='price-desc'?' ORDER BY p.price DESC,p.product_id DESC':' ORDER BY p.created_at DESC,p.product_id DESC');
        $sql.=' LIMIT ?'; $types.='i'; $values[]=max(1,min(100,(int)$limit));
        return $this->rows($sql,$types,$values);
    }
    public function detail($id) {
        $sql='SELECT p.*,c.category_name,sp.shop_name AS seller_name,sp.seller_type,sp.location,sp.description AS seller_description,pm.chest_cm,pm.waist_cm,pm.hips_cm,pm.inseam_cm'.$this->base();
        // Add measurement join before WHERE in shared base.
        $sql=str_replace(" WHERE p.status",' LEFT JOIN product_measurements pm ON pm.product_id=p.product_id WHERE p.status',$sql);
        $rows=$this->rows($sql.' AND p.product_id=? LIMIT 1','i',[(int)$id]);
        return $rows[0]??null;
    }
}
function tgh_catalogue_image($path) {
    $path=ltrim((string)$path,'/');
    if ($path==='' || str_contains($path,'..') || preg_match('~^(https?:|data:|//)~i',$path)) return 'images/products/denim-jacket.svg';
    return $path;
}
