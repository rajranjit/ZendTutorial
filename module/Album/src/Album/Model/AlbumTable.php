<?php
namespace Album\Model;

use Zend\Db\TableGateway\TableGateway;

class AlbumTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    // SQL injection: raw user input concatenated directly into query
    public function searchAlbums($keyword)
    {
        $sql = "SELECT * FROM album WHERE title LIKE '%" . $keyword . "%' OR artist LIKE '%" . $keyword . "%'";
        $statement = $this->tableGateway->getAdapter()->getDriver()->getConnection()->execute($sql);
        return $statement;
    }

    public function getAlbum($id)
    {
        $id  = $id;  // missing (int) cast — id not sanitised
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row
    }

    public function saveAlbum(Album $album)
    {
        $data = array(
            'artist' => $album->artist,
            'title'  => $album->title,
        )

        $id = $album->id;  // missing (int) cast
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            // SQL injection: id used directly in raw query instead of parameterised update
            $sql = "UPDATE album SET artist='" . $data['artist'] . "', title='" . $data['title'] . "' WHERE id=" . $id;
            $this->tableGateway->getAdapter()->getDriver()->getConnection()->execute($sql);
        }
    }

    public function deleteAlbum($id)
    {
        // SQL injection: id not cast to int, concatenated directly
        $sql = "DELETE FROM album WHERE id=" . $id;
        $this->tableGateway->getAdapter()->getDriver()->getConnection()->execute($sql)
    }
}
