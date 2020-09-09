<?php

namespace App\JsonApi\V1\Tasks;

use App\ApplicationLog;
use App\Task;
use App\TaskStatus;
use Carbon\Carbon;
use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function explode;
use function in_array;

use const ROLE_BOAT_MANAGER;
use const TASK_TYPE_PRIMARY;
use const TASK_TYPE_REMARK;

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
        'bridge_position',
        'zone_id',
        'task_type'
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
     * @param Task $task
     * @param $resource
     */
    protected function created(Task $task, $resource)
    {
        if (isset($resource['opener_application_log_id'])) {
            // se mi viene passato un app_log_id, sarà l'app log da cui il task è stato aperto
            Log::debug("Opening Task {$task->id} from APP LOG {$resource['opener_application_log_id']}");

            $application_log = ApplicationLog::findOrFail($resource['opener_application_log_id']);
            $application_log->opened_tasks()->attach($task->id, ['action' => 'open']);
        }
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters) {

        $skipFilters = [];

        // Tipo filters['remark_status'] = 'open|no_actions|local_repaint|total_repaint|closed';
        // se però abbiamo "closed" lo dobbiamo trattare come se si richiedessero tutti i remark chiusi a prescindere dalllo status
        if ($filters->get('remark_status')) {
            $skipFilters = ['status', 'task_type', 'is_open'];

            $remarkStatuses = explode('|', $filters->get('remark_status'));
            $openRemarks = [
                ['task_type', '=', TASK_TYPE_REMARK],
                ['is_open', '=', 1]
            ];

            $closedRemarks = [
                ['task_type', '=', TASK_TYPE_REMARK],
                ['is_open', '=', 0]
            ];

            if (in_array('closed', $remarkStatuses)) {
                unset($remarkStatuses['closed']);
                $query->where($openRemarks)
                    ->whereIn('task_status', $remarkStatuses)
                    ->orWhere($closedRemarks);

            } else {
                $query->where($openRemarks)
                    ->whereIn('task_status', $remarkStatuses);
            }
        }

        if ((!in_array('status', $skipFilters)) && ($status = $filters->get('status'))) {
            $query->whereIn('task_status', explode('|', $status));
        }

        if ((!in_array('task_type', $skipFilters)) && ($task_type = $filters->get('task_type'))) {
            $query->where('task_type', '=', $task_type);
        }

        if ((!in_array('is_open', $skipFilters)) && $filters->has('is_open')) {
            $isOpen = $filters->get('is_open');
            $query->where('is_open', '=', $isOpen);
        }

        if ($project_id = $filters->get('project_id')) {
            $query->where('project_id', '=', $project_id);
        }

        if ($section_id = $filters->get('section_id')) {
            $query->whereIn('section_id', explode('|', $section_id));
        }

        if ($subsection_id = $filters->get('subsection_id')) {
            $query->whereIn('subsection_id', explode('|', $subsection_id));
        }

        if ($intervent_type_id = $filters->get('intervent_type_id')) {
            $query->whereIn('intervent_type_id', explode('|', $intervent_type_id));
        }

        if ($author_id = $filters->get('author_id')) {
            $query->whereIn('author_id', explode('|', $author_id));
        }

        if ($createdAtFrom = $filters->get('created-at-from')) {
            $query->where('created_at', '>=', $createdAtFrom);
        }
        if ($createdAtTo = $filters->get('created-at-to')) {
            $query->where('created_at', '<=', $createdAtTo);
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

        if ($zone_id = $filters->get('zone_id')) {
            $query->where('zone_id', '=', $zone_id);
        }

        if ($opener_application_log_id = $filters->get('opener_application_log_id')) {
            $query->select('tasks.*')
                ->join('applications_logs_tasks', 'applications_logs_tasks.task_id', '=', 'tasks.id')
                ->where('applications_logs_tasks.application_log_id', '=', $opener_application_log_id)
                ->where('applications_logs_tasks.action', '=', 'open');
        }

        // per ottenere lo schema jsonAPI dei Task, forzo così, non è il massimo, vedere se è possibile ottimizzare in qualche modo senza dover ciclare sui risultati
        if ($exclude_opener_application_log_id = $filters->get('exclude_opener_application_log_id')) {
            /** @var ApplicationLog $application_log */
            $application_log = ApplicationLog::findOrFail($exclude_opener_application_log_id);
            $other_tasks_ids = $application_log->getExternallyOpenedRemarksRelatedToMyZones(true);
            $query->whereIn('tasks.id', $other_tasks_ids);
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
