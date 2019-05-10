<?php namespace MatrixAgentsAPI\Modules\IRemNotes\Model;

use MatrixAgentsAPI\Modules\IRemNotes\Interfaces\IRemNoteItemCategoryInterface;

class IRemNoteItemCategory implements IRemNoteItemCategoryInterface
{
    public $markedForDeletion;
    public $categoryId;
    public $categoryName;

    public function isMarkedForDeletion(): bool
    {
        return $this->markedForDeletion;
    }

    public function setMarkedForDeletion(bool $markedForDeletion): IRemNoteItemCategoryInterface
    {
        $this->markedForDeletion = $markedForDeletion;
        return $this;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): IRemNoteItemCategoryInterface
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function setCategoryName(string $categoryName): IRemNoteItemCategoryInterface
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    public function getJson()
    {
        //returns the json equivalent of the current class object
        return get_object_vars($this);
    }

    public function getJsonString(): string
    {
        //returns the json string equivalent of the current class object
        return json_encode(get_object_vars($this));
    }
}
