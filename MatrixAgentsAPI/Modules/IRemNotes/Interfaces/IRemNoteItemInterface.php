<?php namespace MatrixAgentsAPI\Modules\IRemNotes\Interfaces;

use MatrixAgentsAPI\Shared\Models\Interfaces\GenericClassMethodsInterface;

interface IRemNoteItemInterface extends GenericClassMethodsInterface
{

    public function isMarkedForDeletion(): bool;
    public function setMarkedForDeletion(bool $markedForDeletion): IRemNoteItemInterface;
    public function getNoteTitle(): string;
    public function setNoteTitle(string $noteTitle): IRemNoteItemInterface;
    public function getNoteDescription(): string;
    public function setNoteDescription(string $noteDescription): IRemNoteItemInterface;
    public function getNoteId(): string;
    public function setNoteId(string $noteId): IRemNoteItemInterface;
    public function getCategoryTags(): Iterable; //IRemNoteItemCategoryInterface[]
    public function setCategoryTags(Iterable $categoryTags): IRemNoteItemInterface; //IRemNoteItemCategoryInterface[]
    public function getUserId(): string;
    public function setUserId(string $userId): IRemNoteItemInterface;
    public function getCreated(): string;
    public function setCreated(string $created): IRemNoteItemInterface;
    public function getLastUpdated(): string;
    public function setLastUpdated(string $lastUpdated): IRemNoteItemInterface;
}
