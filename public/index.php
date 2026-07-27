<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_login();

require_once APP_ROOT . '/app/models/Project.php';
require_once APP_ROOT . '/app/models/ProjectTask.php';
require_once APP_ROOT . '/app/models/ProjectRisk.php';
require_once APP_ROOT . '/app/models/ProjectBudgetLine.php';
require_once APP_ROOT . '/app/models/ProjectFundingSource.php';
require_once APP_ROOT . '/app/models/ProjectMeeting.php';
require_once APP_ROOT . '/app/models/ProjectTimelineMilestone.php';
require_once APP_ROOT . '/app/models/ProjectDocument.php';
require_once APP_ROOT . '/app/models/ProjectTeamMember.php';

require_once APP_ROOT . '/app/controllers/ProjectsController.php';
require_once APP_ROOT . '/app/controllers/ProjectTasksController.php';
require_once APP_ROOT . '/app/controllers/ProjectRisksController.php';
require_once APP_ROOT . '/app/controllers/ProjectBudgetController.php';
require_once APP_ROOT . '/app/controllers/ProjectFundingController.php';
require_once APP_ROOT . '/app/controllers/ProjectMeetingsController.php';
require_once APP_ROOT . '/app/controllers/ProjectTimelineController.php';
require_once APP_ROOT . '/app/controllers/ProjectDocumentsController.php';
require_once APP_ROOT . '/app/controllers/ProjectTeamController.php';

$page = $_GET['page'] ?? 'projects';

switch ($page) {
    case 'projects':
    case 'dashboard':
        (new ProjectsController())->index();
        break;
    case 'projects_create':
        (new ProjectsController())->create();
        break;
    case 'projects_store':
        (new ProjectsController())->store();
        break;
    case 'projects_show':
        (new ProjectsController())->show();
        break;
    case 'projects_edit':
        (new ProjectsController())->edit();
        break;
    case 'projects_update':
        (new ProjectsController())->update();
        break;
    case 'projects_delete':
        (new ProjectsController())->destroy();
        break;

    case 'project_tasks':
        (new ProjectTasksController())->index();
        break;
    case 'project_tasks_store':
        (new ProjectTasksController())->store();
        break;
    case 'project_tasks_update':
        (new ProjectTasksController())->update();
        break;
    case 'project_tasks_delete':
        (new ProjectTasksController())->destroy();
        break;

    case 'project_risks':
        (new ProjectRisksController())->index();
        break;
    case 'project_risks_store':
        (new ProjectRisksController())->store();
        break;
    case 'project_risks_update':
        (new ProjectRisksController())->update();
        break;
    case 'project_risks_delete':
        (new ProjectRisksController())->destroy();
        break;

    case 'project_budget':
        (new ProjectBudgetController())->index();
        break;
    case 'project_budget_store':
        (new ProjectBudgetController())->store();
        break;
    case 'project_budget_update':
        (new ProjectBudgetController())->update();
        break;
    case 'project_budget_delete':
        (new ProjectBudgetController())->destroy();
        break;

    case 'project_funding':
        (new ProjectFundingController())->index();
        break;
    case 'project_funding_store':
        (new ProjectFundingController())->store();
        break;
    case 'project_funding_update':
        (new ProjectFundingController())->update();
        break;
    case 'project_funding_delete':
        (new ProjectFundingController())->destroy();
        break;

    case 'project_meetings':
        (new ProjectMeetingsController())->index();
        break;
    case 'project_meetings_store':
        (new ProjectMeetingsController())->store();
        break;
    case 'project_meetings_update':
        (new ProjectMeetingsController())->update();
        break;
    case 'project_meetings_delete':
        (new ProjectMeetingsController())->destroy();
        break;

    case 'project_timeline':
        (new ProjectTimelineController())->index();
        break;
    case 'project_timeline_store':
        (new ProjectTimelineController())->store();
        break;
    case 'project_timeline_update':
        (new ProjectTimelineController())->update();
        break;
    case 'project_timeline_delete':
        (new ProjectTimelineController())->destroy();
        break;

    case 'project_documents':
        (new ProjectDocumentsController())->index();
        break;
    case 'project_documents_store':
        (new ProjectDocumentsController())->store();
        break;
    case 'project_documents_download':
        (new ProjectDocumentsController())->download();
        break;
    case 'project_documents_delete':
        (new ProjectDocumentsController())->destroy();
        break;

    case 'project_team':
        (new ProjectTeamController())->index();
        break;
    case 'project_team_store':
        (new ProjectTeamController())->store();
        break;
    case 'project_team_delete':
        (new ProjectTeamController())->destroy();
        break;

    default:
        http_response_code(404);
        echo "Page not found.";
        break;
}
