<?php

namespace App\JsonApi\V1\Tasks;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function explode;
use const ROLE_BOAT_MANAGER;

class Adapter extends AbstractAdapter {

    protected $fillable = [
        'number',
        'title',
        'status',
        'is_open',
        'is_private',
        'description',
        'estimated_hours',
        'worked_hours',
        'for_admins',
        'project_id',
        'section_id',
        'intervent_type_id',
        'subsection_id',
        'x_coord',
        'y_coord',
        'bridge_position'
    ];

    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * @var array
     */
    // mappa il nome della proprieta della risorsa API con il nome del campo nel database
    protected $attributes = ['status' => 'task_status'];

    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging) {
        parent::__construct(new \App\Task(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters) {
        // TODO IMPLEMENTARE L'ACCESSO HAI TASK A LIVELLO DI ACL
        // ricerca per status
        if ($status = $filters->get('status')) {
            $statuses = explode(',', $status);
            foreach ($statuses as $i => $s) {
                if ($i === 0) {
                    $query->where('task_status', '=', "{$s}");
                } else {
                    $query->orWhere('task_status', '=', "{$s}");
                }
            }
        }

        // ricerca per project_id
        if ($project_id = $filters->get('project_id')) {
            $query->where('project_id', '=', "{$project_id}");
        }

        // ricerca per section_id
        if ($section_id = $filters->get('section_id')) {
            $sections = explode(',', $section_id);
            foreach ($sections as $i => $section) {
                if ($i === 0) {
                    $query->where('section_id', '=', "{$section}");
                } else {
                    $query->orWhere('section_id', '=', "{$section}");
                }
            }
        }

        if ($subsection_id = $filters->get('subsection_id')) {
            $query->where('subsection_id', '=', "{$subsection_id}");
        }

        // ricerca per intervent_type_id
        if ($intervent_type_id = $filters->get('intervent_type_id')) {
            $intervents = explode(',', $intervent_type_id);
            foreach ($intervents as $i => $intertervent) {
                if ($i === 0) {
                    $query->where('intervent_type_id', '=', "{$intertervent}");
                } else {
                    $query->orWhere('intervent_type_id', '=', "{$intertervent}");
                }
            }
        }
        // ricerca per author_id
        if ($author_id = $filters->get('author_id')) {
            $authors = explode(',', $author_id);
            foreach ($authors as $i => $author) {
                if ($i === 0) {
                    $query->where('author_id', '=', "{$author}");
                } else {
                    $query->orWhere('author_id', '=', "{$author}");
                }
            }
        }

        // ricerca per created-at from
        if ($createdAtFrom = $filters->get('created-at-from')) {
            $query->where('created_at', '>=', "{$createdAtFrom}");
        }
        // ricerca per created-at to
        if ($createdAtTo = $filters->get('created-at-to')) {
            $query->where('created_at', '<=', "{$createdAtTo}");
        }

        if ($updated_at_from = $filters->get('updated-at-from')) {
            $query->where('updated_at', '>=', $updated_at_from);
        }
        if ($updated_at_to = $filters->get('updated-at-to')) {
            $query->where('updated_at', '<=', $updated_at_to);
        }

        // internal_progressive_numbers, mi vengono passati così 3, 7, 9-12, ...
        if ($prog_nums = $filters->get('prog_nums')) {
            $numbers = explode(',', $prog_nums);
            $intervals = array_filter($numbers, function ($elem) {
                return Str::contains($elem, '-');
            });
            if (!empty($intervals)) {
                $intervals_exploded = array_map(function ($elem) {
                    $nums = [];
                    list($start, $stop) = explode('-', $elem); // "9-12" => [9, 12]
                    for ($i = $start; $i <= $stop; $i++) {
                        $nums[] = (string)$i;
                    }
                    return $nums;
                }, $intervals);
                $numbers = Arr::except($numbers, array_keys($intervals));
                $numbers = array_merge($numbers, Arr::flatten($intervals_exploded));
            }

            $query->whereIn('internal_progressive_number', $numbers);
        }

        // ricerca is_open
        if ($filters->has('is_open')) {
            $isOpen = $filters->get('is_open');
            $query->where('is_open', '=', $isOpen);
        }

        $user = \Auth::user();

        // ricerca is_private
        if ($filters->has('is_private')) {
            $is_private = $filters->get('is_private');
            if ($user && $user->is_storm) {
                $query->where('is_private', '=', $is_private);
            }
        }

        /** restringe il recordset in caso di mancanza di permessi */
        if ($user && !$user->can(PERMISSION_ADMIN)) {
            // L'utente loggato non e' un admin
            if (!$user->is_storm) {
                $query->where('is_private', '=', false);
            }

            // SE SI TRATTA DI UN DIPENDENTE  ALLORA MOSTRO SOLO QUELLI LEGATI A project_user
            if ($user->hasRole(ROLE_WORKER)) {
                $query->Join('projects', 'tasks.project_id', '=', 'projects.id')
                        /* ->where('projects.project_status', '=', PROJECT_STATUS_OPEN) */
                        ->whereExists(function ($q) {
                            $user = \Auth::user();
                            $q->from('project_user')->whereRaw("project_user.user_id = {$user->id}");
                        });
            }

            // RUOLO BOOT MANAGER potrebbe essere questo il ruolo da assegnare all'equipaggio ? da discutere con Danilo
            if ($user->hasRole(ROLE_BOAT_MANAGER)) {
                // TODO deve vedere solo i task relazionati alla barca
//                $query->whereHas('author', function($q) use ($user)
//                     {
//                        $q->whereUser_id($user->id);
//                     });
            }
        }
    }

}
