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
        $meetingAttendees = [];
        foreach ($meetingList as $m) {
            $meetingAttendees[(int)$m['meeting_id']] = $this->meetings->attendees((int)$m['meeting_id']);
        }
        $editMeeting = null;
        $editAttendeeIds = [];
        if (!empty($_GET['edit_id'])) {
            $editMeeting = $this->meetings->find((int)$_GET['edit_id']);
            if ($editMeeting) {
                $editAttendeeIds = array_column($this->meetings->attendees((int)$editMeeting['meeting_id']), 'person_id');
            }
        }
        $people = $this->peopleOptions();
        $emailSuccess = $_SESSION['meeting_email_success'] ?? null;
        $emailError = $_SESSION['meeting_email_error'] ?? null;
        unset($_SESSION['meeting_email_success'], $_SESSION['meeting_email_error']);
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

    public function emailMinutes(): void
    {
        $id = (int)($_POST['meeting_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $meeting = $this->meetings->find($id);
            $project = $this->projects->find($projectId);

            if (!$meeting || !$project) {
                $_SESSION['meeting_email_error'] = 'Meeting not found.';
            } elseif (trim((string)($meeting['minutes'] ?? '')) === '') {
                $_SESSION['meeting_email_error'] = 'This meeting has no minutes to send.';
            } else {
                $attendees = $this->meetings->attendees($id);
                $recipients = array_values(array_unique(array_filter(array_map(
                    static fn(array $a) => trim((string)($a['email'] ?? '')),
                    $attendees
                ))));

                if (!$recipients) {
                    $_SESSION['meeting_email_error'] = 'No participants with an email address were found for this meeting.';
                } else {
                    $sent = $this->sendMinutesEmail($project, $meeting, $recipients);
                    if ($sent) {
                        $_SESSION['meeting_email_success'] = 'Minutes emailed to ' . count($recipients) . ' participant(s).';
                    } else {
                        $_SESSION['meeting_email_error'] = 'Failed to send the email. Please check the server mail configuration.';
                    }
                }
            }
        }

        header('Location: /index.php?page=project_meetings&project_id=' . $projectId);
        exit;
    }

    private function sendMinutesEmail(array $project, array $meeting, array $recipients): bool
    {
        $meetingDate = date('m/d/Y g:i A', strtotime((string)$meeting['meeting_date']));
        $subject = 'Meeting Minutes: ' . $project['project_name'] . ' — ' . $meetingDate;

        $body = "Meeting minutes for {$project['project_name']}\n";
        $body .= "Date: {$meetingDate}\n";
        if (!empty($meeting['meeting_type'])) {
            $body .= "Type: {$meeting['meeting_type']}\n";
        }
        if (!empty($meeting['location'])) {
            $body .= "Location: {$meeting['location']}\n";
        }
        if (!empty($meeting['agenda'])) {
            $body .= "\nAgenda:\n{$meeting['agenda']}\n";
        }
        $body .= "\nMinutes:\n{$meeting['minutes']}\n";

        $fromEmail = $this->pdo->query("SELECT primary_contact_email FROM organization_settings ORDER BY id ASC LIMIT 1")->fetchColumn();
        $fromEmail = trim((string)($fromEmail ?: 'no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost')));

        $headers = "From: " . $fromEmail . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $allSent = true;
        $attempted = 0;
        foreach ($recipients as $to) {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $attempted++;
            $allSent = mail($to, $subject, $body, $headers) && $allSent;
        }

        return $attempted > 0 && $allSent;
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
