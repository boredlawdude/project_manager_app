<?php
declare(strict_types=1);

final class ProjectMeeting
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_meetings WHERE project_id = ? ORDER BY meeting_date DESC");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_meetings WHERE meeting_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function attendees(int $meetingId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.person_id, a.attended, CONCAT(p.first_name,' ',p.last_name) AS name
            FROM project_meeting_attendees a
            JOIN people p ON p.person_id = a.person_id
            WHERE a.meeting_id = ?
            ORDER BY name
        ");
        $stmt->execute([$meetingId]);
        return $stmt->fetchAll();
    }

    public function create(int $projectId, array $d, ?int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_meetings (
                project_id, meeting_date, meeting_type, location, agenda, minutes, created_by_person_id
            ) VALUES (
                :project_id, :meeting_date, :meeting_type, :location, :agenda, :minutes, :created_by_person_id
            )
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'meeting_date' => $d['meeting_date'],
            'meeting_type' => $d['meeting_type'] ?: null,
            'location' => $d['location'] ?: null,
            'agenda' => $d['agenda'] ?: null,
            'minutes' => $d['minutes'] ?: null,
            'created_by_person_id' => $createdBy,
        ]);
        $meetingId = (int)$this->db->lastInsertId();
        $this->syncAttendees($meetingId, $d['attendee_person_ids'] ?? []);
        return $meetingId;
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare("
            UPDATE project_meetings SET
                meeting_date = :meeting_date, meeting_type = :meeting_type, location = :location,
                agenda = :agenda, minutes = :minutes
            WHERE meeting_id = :meeting_id
        ");
        $stmt->execute([
            'meeting_date' => $d['meeting_date'],
            'meeting_type' => $d['meeting_type'] ?: null,
            'location' => $d['location'] ?: null,
            'agenda' => $d['agenda'] ?: null,
            'minutes' => $d['minutes'] ?: null,
            'meeting_id' => $id,
        ]);
        $this->syncAttendees($id, $d['attendee_person_ids'] ?? []);
    }

    private function syncAttendees(int $meetingId, array $personIds): void
    {
        $this->db->prepare("DELETE FROM project_meeting_attendees WHERE meeting_id = ?")->execute([$meetingId]);
        $stmt = $this->db->prepare("INSERT INTO project_meeting_attendees (meeting_id, person_id) VALUES (?, ?)");
        foreach (array_unique(array_map('intval', $personIds)) as $pid) {
            if ($pid > 0) {
                $stmt->execute([$meetingId, $pid]);
            }
        }
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_meetings WHERE meeting_id = ?")->execute([$id]);
    }
}
