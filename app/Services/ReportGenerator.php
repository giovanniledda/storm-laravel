<?php

namespace App\Services;

use function __;
use App\Project;
use Illuminate\Http\Response;
use Net7\DocsGenerator\DocsGenerator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ReportGenerator
{
    /**
     * @param string $template
     * @param Project $project
     * @param null $subtype
     * @return Response|mixed
     * @throws \Exception
     */
    public static function reportGenerationProcess(string $template, Project &$project, $subtype = null)
    {
        $dg = new DocsGenerator($template, $project);

        if (isset($dg) && ! $dg->checkTemplateCategory()) {
            $msg = __("Template :name not valid (there's no such a Model on DB)!", ['name' => $template]);
            throw new \Exception($msg);
        }

        // ...e che ci sia il template associato nel filesystem.
        try {
            $dg->checkIfTemplateFileExistsWithTemplateObjectCheck(true);
        } catch (FileNotFoundException $e) {
            $msg = __(
                "Template :name not found (you're searching on ':e_msg')!",
                ['name' => $template, 'e_msg' => $e->getMessage()]
            );
            throw new \Exception($msg);
        }

        try {
            $document = $dg->startProcess();
        } catch (\Exception $e) {
            $msg = __("Error generating report (':e_msg')!", ['e_msg' => $e->getMessage()]);
            throw new \Exception($msg);
        }

        $document->subtype = $subtype;

        return $document;
    }
}
