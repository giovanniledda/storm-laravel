<?php


namespace Net7\Documents;

use Illuminate\Database\Eloquent\Model;

class DocumentableModel extends Model {


    /**
     * Returns a string with the path where the files should be saved
     *
     * Override it in your model file if needed
     */
    public function getMediaPath($document){
        return DIRECTORY_SEPARATOR . 'documents'. DIRECTORY_SEPARATOR .  $this->id  . DIRECTORY_SEPARATOR ;

    }

    /**
     * The relation with the Document element, a morphMany let us relate multiple models with Document
     */

    public function documents() {
        return $this->morphMany('Net7\Documents\Document', 'documentable');
    }

    /**
     * A utility method that makes you add a document to the model specifying the document type
     *
     * Override it in your model file if needed
     */
    public function addDocumentWithType(\Net7\Documents\Document $doc, $type) {
        if ($type) {
            $doc->type = $type;
        } else {
            $doc->type = \Net7\Documents\Document::GENERIC_DOCUMENT_TYPE;
        }
        $this->documents()->save($doc);
    }
}
