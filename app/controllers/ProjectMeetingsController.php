<?php
declare(strict_types=1);

final class ProjectMeetingsController
{
    private PDO $pdo;
    private ProjectMeeting $meetings;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->meetings = new ProjectMeeting($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $meetingList = $this->meetings->listByProject($projectId);
        $editMeeting = null;
        $editAttendeeIds = [];
        if (!empty($_GET['edit_id'])) {
            $editMeeting = $this->meetings->find((int)$_GET['edit_id']);
            if ($editMeeting) {
                $editAttendeeIds = array_column($this->meetings->attendees((int)$editMeeting['meeting_id']), 'person_id');
            }
        }
        $people = $this->peopleOptions();
        require APP_ROOT . '/app/views/project_meetings/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['meeting_date'] ?? '')) !== '') {
            $this->meetings->create($projectId, $this->collect(), current_person_id());
        }
        header('Location: /index.php?page=project_meetings&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['meeting_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->meetings->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_meetings&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['meeting_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->meetings->delete($id);
        }
        header('Location: /index.php?page=project_meetings&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'meeting_date' => trim((string)($_POST['meeting_date'] ?? '')),
            'meeting_type' => trim((string)($_POST['meeting_type'] ?? '')),
            'location' => trim((string)($_POST['location'] ?? '')),
            'agenda' => trim((string)($_POST['agenda'] ?? '')),
            'minutes' => trim((string)($_POST['minutes'] ?? '')),
            'attendee_person_ids' => array_map('intval', (array)($_POST['attendee_person_ids'] ?? [])),
        ];
    }

    private function peopleOptions(): array
    {
        return $this->pdo->query("SELECT person_id, CONCAT(first_name,' ',last_name) AS name FROM people WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
