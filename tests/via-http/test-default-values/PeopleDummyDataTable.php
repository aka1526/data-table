<?php
/**
 * @package Data Table
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT MIT
 */


/**
 * Default values for list data table.
 *
 * @author Vee W.
 */
class PeopleDummyDataTable extends \Rundiz\DataTable\DataTable
{


    /**
     * @var string My table name.
     */
    public $myTable;


    /**
     * {@inheritDoc}
     */
    protected function columnDefault($row, $column_unique_name)
    {
        switch ($column_unique_name) {
            default:
                if (isset($row->{$column_unique_name})) {
                    return $row->{$column_unique_name};
                }
        }
    }// columnDefault


    /**
     * Special column method for "name".
     * 
     * @param object $row Data from PDO in each row from fetchAll() method.
     * @return string Return column content. Use return, not echo.
     */
    protected function columnName($row)
    {
        return $row->first_name . ' ' . $row->last_name;
    }// columnName


    /**
     * {@inheritDoc}
     */
    protected function getColumns()
    {
        return [
            'name' => 'Full name',
            'email' => 'Email',
            'gender' => 'Gender',
            'ip_address' => 'IP Address',
        ];
    }// getColumns


    /**
     * {@inheritDoc}
     */
    public function prepareData()
    {
        if (!class_exists('\\Rundiz\\Pagination\\Pagination') || !is_object($this->Pagination)) {
            throw new \Exception('The class \\Rundiz\\Pagination\\Pagination is not exists, please install rundiz/pagination class.');
        }

        $current_page = $this->getCurrentPage();

        $sql = 'SELECT COUNT(*) FROM `' . $this->myTable . '`';
        $stmt = $this->Database->PDO->prepare($sql);
        $stmt->execute();
        $total_items = $stmt->fetchColumn();
        unset($stmt);

        // order (column) and sort (asc, desc)
        $order = 'id';
        if (isset($_GET[$this->orderQueryName])) {
            if (in_array($_GET[$this->orderQueryName], ['id', 'first_name', 'last_name', 'email', 'gender', 'ip_address'])) {
                $order = $_GET[$this->orderQueryName];
            }
        }
        $sort = 'ASC';
        if (isset($_GET[$this->sortQueryName])) {
            if (in_array(strtoupper($_GET[$this->sortQueryName]), ['ASC', 'DESC'])) {
                $sort = strtoupper($_GET[$this->sortQueryName]);
            }
        }

        $sql = str_replace('COUNT(*)', '*', $sql);
        $sql .= ' ORDER BY `' . $order . '` ' . $sort;
        $sql .= ' LIMIT ' . (($current_page - 1) * $this->itemsPerPage) . ', ' . $this->itemsPerPage;
        $stmt = $this->Database->PDO->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();
        unset($order, $sort, $sql, $stmt);

        $this->Pagination->base_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $this->paginationQueryName . '=%PAGENUMBER%';

        // set total items found and data items for listing to the properties.
        $this->totalItems = $total_items;
        $this->dataItems = $items;
        unset($current_page, $items, $total_items);
    }// prepareData


}
