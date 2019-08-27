<?php
namespace Net7\Documents;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Models\Media as BaseMedia;
use \Venturecraft\Revisionable\RevisionableTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;


class Document extends Model implements HasMedia
{
    // see https://docs.spatie.be/laravel-medialibrary/v7/basic-usage/preparing-your-model
    use HasMediaTrait;

    // see https://github.com/VentureCraft/revisionable
    use RevisionableTrait;

    // TODO: metterli a configurazione

    public const GENERIC_DOCUMENT_TYPE = 'generic_document';
    public const DETAILED_IMAGE_TYPE = 'detailed_image';
    public const GENERIC_IMAGE_TYPE = 'generic_image';
    public const ADDITIONAL_IMAGE_TYPE = 'additional_image';


    // protected $revisionCleanup = true; //Remove old revisions (works only when used with $historyLimit)
    // protected $historyLimit = 500; //Maintain a maximum of 500 changes at any point of time, while cleaning up old revisions.

    protected $fillable = [
        'title', 'type'
    ];


    public function __construct(array $attributes = [])
    {
        // this is when you create a Document from PHP (not via Json:API)
        if (isset($attributes['file'])){
            $path = $attributes['file']->getPathName();
            $name = $attributes['file']->getClientOriginalName();


            // TODO: diversificare lo storage basandosi sul tipo del modello collegato\
            // TODO: per storm fare collections 'projects' quindi anche quello deve essere parametrico
            $media = $this->addMedia($path)->usingFileName($name)->toMediaCollection('documents', env('MEDIA_DISK', 'local'));

            unset ($attributes['file']);
        }
        parent::__construct($attributes);

    }
    /**
     * definisce un'immagine thumb
     */
    public function registerMediaConversions(BaseMedia $media = null)
    {
        // TODO mettere i valori in config

        // TODO: non fare le miniature per alcune collections
        $this->addMediaConversion('thumb')
              ->width(368)
              ->height(232)
              ->sharpen(10);
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }


    public function getFile(){
        return $this->getUrl();
       // return $this->getFirstMedia('documents')->getPath();
    }


    public function documentable(): \Illuminate\Database\Eloquent\Relations\MorphTo {
        return $this->morphTo();
    }


    static function createUploadedFileFromBase64($base64File,  $filename){
        if ($base64File) {
            $tmpFilename = uniqid('phpfile_') ;
            $tmpFileFullPath = '/tmp/'. $tmpFilename;
            $h = fopen ($tmpFileFullPath, 'w');
            $decoded = base64_decode($base64File, true);
            fwrite($h, $decoded, strlen($decoded));
            fclose($h);

        return new \Symfony\Component\HttpFoundation\File\UploadedFile( $tmpFileFullPath, $filename, null ,null, true);

        } else {
            //TODO: raise exception
            return "ERRORE!";
        }

    }


    public function getShowApiUrl(){
        return $this->id; // su richiesta di Giovanni Miscali per semplificare la parte dello storage in assenza di connesione
      //  return route('api:v1:documents.show', [$this->id]);
    }

    public function getThumbUrl() {

    }

}
