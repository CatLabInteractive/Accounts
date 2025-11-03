<?php

namespace CatLab\Accounts\Models;

use CatLab\Accounts\MapperFactory;

class Lock
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public static function create($name, $timeout = 5)
    {
        return MapperFactory::getLockMapper()->create($name);
    }

    /**
     *
     */
    public function release()
    {
        MapperFactory::getLockMapper()->delete($this);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Lock
     */
    public function setId(int $id): Lock
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Lock
     */
    public function setName(string $name): Lock
    {
        $this->name = $name;
        return $this;
    }
}
