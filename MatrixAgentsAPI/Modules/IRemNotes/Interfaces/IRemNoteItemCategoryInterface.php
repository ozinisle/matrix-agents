<?php namespace MatrixAgentsAPI\Modules\IRemNotes\Interfaces;

use MatrixAgentsAPI\Shared\Models\Interfaces\GenericClassMethodsInterface;

interface IRemNoteItemCategoryInterface extends GenericClassMethodsInterface
{
    public function isMarkedForDeletion(): bool;
    public function setMarkedForDeletion(bool $markedForDeletion): IRemNoteItemCategoryInterface;
    public function getCategoryId(): string;
    public function setCategoryId(string $categoryId): IRemNoteItemCategoryInterface;
    public function getCategoryName(): string;
    public function setCategoryName(string $categoryName): IRemNoteItemCategoryInterface;
}
