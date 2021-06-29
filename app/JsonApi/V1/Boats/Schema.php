<?php

namespace App\JsonApi\V1\Boats;

use App\Models\Section;
use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{
    /**
     * @var string
     */
    protected $resourceType = 'boats';

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    public function getPrimaryMeta($resource)
    {
        //App\Boat resource

        $generic_documents = $resource->generic_documents;

        // $giu = [];
        // foreach ($generic_images as $i){
        //     $giu []= $i->getShowApiUrl();
        // }

        $gdu = [];
        foreach ($generic_documents as $i) {
            $tmp = [
                'uri' => $i->getShowApiUrl(),
                'title' => $i->title,
                'mime_type' => $i->media->first()->mime_type, // TODO: get MIME TYPE
            ];
            $gdu[] = $tmp;
        }

        $image = $resource->generic_images->last();
        if (! $image) {
            $image = $resource->detailed_images->first();
        }

        return [
            'generic_documents' => $gdu,
            'image' => $image ? $image->getShowApiUrl() : null,
        ];
        // TODO : mettere sia il link documentale all'immagine della barca che il project_id
    }

    public function getInclusionMeta($resource)
    {
        return $this->getPrimaryMeta($resource);
    }

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        $n_utenti = 0;
        $n_sezioni = 0;

        $project_active = $resource->projects()
                ->whereIn('project_status', [PROJECT_STATUS_OPERATIONAL, PROJECT_STATUS_IN_SITE])
                ->orderBy('created_at', 'DESC')
                ->first();

        if ($project_active) {
            $location = $resource->projects()
                     ->select('sites.name', 'sites.location')
                     ->Join('sites', 'projects.site_id', '=', 'sites.id')
                     ->where('projects.id', '=', $project_active->id)
                     ->first();

            $project_active->location = $location;

            // se il progetto e' attivo allora aggiungo anche il numero degli utenti del progetto
            $n_utenti = $resource->projects()
                     ->select('project_user.user_id')
                     ->RightJoin('project_user', 'project_user.project_id', '=', 'projects.id')
                     ->where('project_user.project_id', '=', $project_active->id)->count();
            $n_sezioni = Section::where('boat_id', '=', $resource->id)->count();
            $project_active->sections = $n_sezioni;
            $project_active->users = $n_utenti;
        }

        $owner = $resource
                ->select('users.name', 'users.surname', 'users.id')
                ->Join('boat_user', 'boat_user.boat_id', '=', 'boats.id')
                ->Join('professions', 'boat_user.profession_id', '=', 'professions.id')
                ->Join('users', 'users.id', '=', 'boat_user.user_id')
                ->where('professions.slug', '=', 'owner')
                ->where('boats.id', '=', $resource->id)
                ->first();

        return [
            'name' => $resource->name,
            'registration_number' => $resource->registration_number,
            'flag' => $resource->flag,
            'manufacture_year' => $resource->manufacture_year,
            'length' => $resource->length,
            'draft' => $resource->draft,
            'beam' => $resource->beam,
            'boat_type' => $resource->boat_type,
            'site_id' => $resource->site_id,
            'project'  => $project_active,
            'owner'=> $owner,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }
}
