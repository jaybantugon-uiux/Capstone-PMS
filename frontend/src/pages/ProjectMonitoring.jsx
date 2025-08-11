import React, { useState, useEffect } from "react";
import Sidebar from "../components/Sidebar";
import { Container, Row, Col, Card, Button, Table, Modal, Form } from "react-bootstrap";
import { PersonCircle } from "react-bootstrap-icons";
import AddCircleOutlineIcon from "@mui/icons-material/AddCircleOutline";
import ArchiveIcon from "@mui/icons-material/Archive";
import RestoreIcon from "@mui/icons-material/Restore";
import SearchIcon from "@mui/icons-material/Search";
import FilterListIcon from "@mui/icons-material/FilterList";
import "../css/Dashboard.css";

const ProjectMonitoring = () => {
  const [userRole, setUserRole] = useState("");
  const [projects, setProjects] = useState([]);
  const [newProject, setNewProject] = useState({ name: "", details: "" });
  const [showUploadModal, setShowUploadModal] = useState(false);
  const [showArchiveModal, setShowArchiveModal] = useState(false);
  const [showRestoreModal, setShowRestoreModal] = useState(false);
  const [showSearchModal, setShowSearchModal] = useState(false);
  const [showFilterModal, setShowFilterModal] = useState(false);
  // Added: state for project details modal and per-day items
  const [showProjectModal, setShowProjectModal] = useState(false);
  const [currentProject, setCurrentProject] = useState(null);
  const [availableDates, setAvailableDates] = useState([]);
  const [selectedDate, setSelectedDate] = useState("");
  const [dailyItemsByProject, setDailyItemsByProject] = useState({}); // { [projectId]: { [yyyy-mm-dd]: { reports:[], issues:[], photos:[] } } }
  const [showReportModal, setShowReportModal] = useState(false);
  const [showIssueModal, setShowIssueModal] = useState(false);
  const [showPhotoModal, setShowPhotoModal] = useState(false);
  const [reportForm, setReportForm] = useState({ title: "", details: "" });
  const [issueForm, setIssueForm] = useState({ title: "", severity: "low", details: "" });
  const [photoFiles, setPhotoFiles] = useState([]);

  useEffect(() => {
    const userData = localStorage.getItem("user");
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }

    fetchProjects();
  }, []);

  const fetchProjects = async () => {
    try {
      const response = await fetch("http://localhost:8000/api/projects", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
      const data = await response.json();
      setProjects(data.projects);
    } catch (error) {
      console.error("Error fetching projects:", error);
    }
  };

  const handleUploadSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch("http://localhost:8000/api/projects/upload", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify(newProject),
      });
      const data = await response.json();
      if (data.status === "success") {
        setProjects([...projects, data.project]);
        setNewProject({ name: "", details: "" });
        setShowUploadModal(false);
      }
    } catch (error) {
      console.error("Error uploading project:", error);
    }
  };

  const handleArchiveProject = async (projectId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/projects/${projectId}/archive`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
      const data = await response.json();
      if (data.status === "success") {
        setProjects(projects.filter((project) => project.id !== projectId));
      }
    } catch (error) {
      console.error("Error archiving project:", error);
    }
  };

  const handleRestoreProject = async (projectId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/projects/${projectId}/restore`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
      const data = await response.json();
      if (data.status === "success") {
        setProjects([...projects, data.project]);
      }
    } catch (error) {
      console.error("Error restoring project:", error);
    }
  };

  const handleProjectChange = (e) => {
    const { name, value } = e.target;
    setNewProject({ ...newProject, [name]: value });
  };

  // Added: helpers for project modal and daily data
  const formatDateKey = (d) => {
    const dateObj = typeof d === "string" ? new Date(d) : d;
    return new Date(Date.UTC(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate()))
      .toISOString()
      .slice(0, 10);
  };

  const generateDateRange = (startStr, endStr) => {
    try {
      const start = new Date(startStr);
      const end = endStr ? new Date(endStr) : start;
      if (isNaN(start.getTime())) return [];
      const days = [];
      const cur = new Date(start);
      while (cur <= end) {
        days.push(formatDateKey(cur));
        cur.setDate(cur.getDate() + 1);
      }
      return days;
    } catch {
      return [];
    }
  };

  const ensureDailyState = (projectId, dateKey) => {
    setDailyItemsByProject((prev) => {
      const projectMap = prev[projectId] || {};
      const day = projectMap[dateKey] || { reports: [], issues: [], photos: [] };
      if (prev[projectId] && projectMap[dateKey]) return prev;
      return {
        ...prev,
        [projectId]: {
          ...projectMap,
          [dateKey]: day,
        },
      };
    });
  };

  const openProjectModal = (project) => {
    setCurrentProject(project);
    const dates = generateDateRange(project.start_date, project.end_date);
    const todayKey = formatDateKey(new Date());
    const defaultDate = dates.includes(todayKey) ? todayKey : dates[0] || todayKey;
    setAvailableDates(dates.length ? dates : [defaultDate]);
    setSelectedDate(defaultDate);
    ensureDailyState(project.id, defaultDate);
    setShowProjectModal(true);
  };

  const handleDateChange = (e) => {
    const nextDate = e.target.value;
    setSelectedDate(nextDate);
    if (currentProject) ensureDailyState(currentProject.id, nextDate);
  };

  const handleReportSubmit = (e) => {
    e.preventDefault();
    if (!currentProject || !selectedDate || !reportForm.title.trim()) return;
    setDailyItemsByProject((prev) => {
      const projectMap = prev[currentProject.id] || {};
      const day = projectMap[selectedDate] || { reports: [], issues: [], photos: [] };
      return {
        ...prev,
        [currentProject.id]: {
          ...projectMap,
          [selectedDate]: {
            ...day,
            reports: [
              ...day.reports,
              {
                id: Date.now(),
                title: reportForm.title,
                details: reportForm.details,
                created_at: new Date().toISOString(),
              },
            ],
          },
        },
      };
    });
    setReportForm({ title: "", details: "" });
    setShowReportModal(false);
  };

  const handleIssueSubmit = (e) => {
    e.preventDefault();
    if (!currentProject || !selectedDate || !issueForm.title.trim()) return;
    setDailyItemsByProject((prev) => {
      const projectMap = prev[currentProject.id] || {};
      const day = projectMap[selectedDate] || { reports: [], issues: [], photos: [] };
      return {
        ...prev,
        [currentProject.id]: {
          ...projectMap,
          [selectedDate]: {
            ...day,
            issues: [
              ...day.issues,
              {
                id: Date.now(),
                title: issueForm.title,
                severity: issueForm.severity,
                details: issueForm.details,
                created_at: new Date().toISOString(),
              },
            ],
          },
        },
      };
    });
    setIssueForm({ title: "", severity: "low", details: "" });
    setShowIssueModal(false);
  };

  const handlePhotoSubmit = (e) => {
    e.preventDefault();
    if (!currentProject || !selectedDate || photoFiles.length === 0) return;
    const mapped = photoFiles.map((file) => ({
      id: `${Date.now()}_${file.name}`,
      name: file.name,
    }));
    setDailyItemsByProject((prev) => {
      const projectMap = prev[currentProject.id] || {};
      const day = projectMap[selectedDate] || { reports: [], issues: [], photos: [] };
      return {
        ...prev,
        [currentProject.id]: {
          ...projectMap,
          [selectedDate]: {
            ...day,
            photos: [...day.photos, ...mapped],
          },
        },
      };
    });
    setPhotoFiles([]);
    setShowPhotoModal(false);
  };

  const roleLabelMap = {
    manager: "Project Manager",
    coordinator: "Site Coordinator",
    client: "Client",
  };

  return (
    <div className="dashboard-layout">
      <main className="dashboard-main">
        <Container fluid className="h-100 py-4 px-4">
          <Row className="mb-4">
            <Col className="d-flex align-items-center justify-content-between">
              <div className="dashboard-sidebar-wrapper">
                <Sidebar className="dashboard-sidebar" />
              </div>

              <div className="d-flex align-items-center gap-2">
                <PersonCircle size={40} />
                <Form.Select
                  size="sm"
                  className="border-0 bg-transparent"
                  style={{ width: "auto" }}
                  disabled
                >
                  <option>{roleLabelMap[userRole] || "User"}</option>
                </Form.Select>
              </div>
            </Col>
          </Row>

          <Row className="mb-4">
            <Col className="d-flex justify-content-end gap-2">
              <Button
                variant="primary"
                className="upload-project-modal"
                onClick={() => setShowUploadModal(true)}
              >
                <AddCircleOutlineIcon className="me-2" />
                <span className="d-none d-md-inline">Upload Project</span>
              </Button>
              <Button
                variant="danger"
                className="archive-project-modal"
                onClick={() => setShowArchiveModal(true)}
              >
                <ArchiveIcon className="me-2" />
                <span className="d-none d-md-inline">Archive Project</span>
              </Button>
              <Button
                variant="success"
                className="restore-project-modal"
                onClick={() => setShowRestoreModal(true)}
              >
                <RestoreIcon className="me-2" />
                <span className="d-none d-md-inline">Restore Project</span>
              </Button>
              <Button
                variant="primary"
                className="search-project-modal"
                onClick={() => setShowSearchModal(true)}
              >
                <SearchIcon className="me-2" />
                <span className="d-none d-md-inline">Search Project</span>
              </Button>
              <Button
                variant="primary"
                className="filter-project-modal"
                onClick={() => setShowFilterModal(true)}
              >
                <FilterListIcon className="me-2" />
                <span className="d-none d-md-inline">Filter Projects</span>
              </Button>
            </Col>
          </Row>

          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header>
                  <h5>Project List</h5>
                </Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Workers</th>
                        <th>Cost</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {projects.map((project, index) => (
                        <tr key={project.id}>
                          <td>{index + 1}</td>
                          <td>{project.name}</td>
                          <td>{project.client}</td>
                          <td>{project.type}</td>
                          <td>{project.workers}</td>
                          <td>{project.cost}</td>
                          <td>{project.start_date ? new Date(project.start_date).toLocaleDateString() : "-"}</td>
                          <td>{project.end_date ? new Date(project.end_date).toLocaleDateString() : "-"}</td>
                          <td>{project.status}</td>
                          <td>
                            <Button variant="info" size="sm" onClick={() => openProjectModal(project)}>View</Button>
                            <Button variant="primary" size="sm" className="mx-1">Edit</Button>
                            <Button variant="danger" size="sm" onClick={() => handleArchiveProject(project.id)}>Archive</Button>
                            <Button variant="success" size="sm" onClick={() => handleRestoreProject(project.id)}>Restore</Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          {/* Upload Project Modal */}
          <Modal show={showUploadModal} onHide={() => setShowUploadModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Upload Project</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleUploadSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Project Name</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    value={newProject.name}
                    onChange={handleProjectChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Details</Form.Label>
                  <Form.Control
                    type="text"
                    name="details"
                    value={newProject.details}
                    onChange={handleProjectChange}
                    required
                  />
                </Form.Group>
                <Button variant="primary" type="submit">
                  Upload
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Search Project Modal */}
          <Modal show={showSearchModal} onHide={() => setShowSearchModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Search Project</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form>
                <Form.Group className="mb-3">
                  <Form.Label>Project Name</Form.Label>
                  <Form.Control type="text" placeholder="Enter project name" />
                </Form.Group>
                <Button variant="primary" type="button">
                  Search
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Filter Project Modal */}
          <Modal show={showFilterModal} onHide={() => setShowFilterModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Filter Projects</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form>
                <Form.Group className="mb-3">
                  <Form.Label>Client</Form.Label>
                  <Form.Control type="text" placeholder="Enter client name" />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Status</Form.Label>
                  <Form.Select>
                    <option value="">All</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="archived">Archived</option>
                  </Form.Select>
                </Form.Group>
                <Button variant="primary" type="button">
                  Apply Filters
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Project Details Modal */}
          <Modal show={showProjectModal} onHide={() => setShowProjectModal(false)} size="lg" centered>
            <Modal.Header closeButton>
              <Modal.Title>Project Details</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              {currentProject && (
                <div className="mb-3">
                  <div className="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                      <h5 className="mb-1">{currentProject.name}</h5>
                      <div className="text-muted">{currentProject.description || currentProject.details || "No description"}</div>
                      <div className="small mt-2">
                        <span className="me-3">Start: {currentProject.start_date ? new Date(currentProject.start_date).toLocaleDateString() : "-"}</span>
                        <span className="me-3">End: {currentProject.end_date ? new Date(currentProject.end_date).toLocaleDateString() : "-"}</span>
                        <span>Status: {currentProject.archived ? "Archived" : "Active"}</span>
                      </div>
                    </div>
                    <div className="d-flex gap-2">
                      <Button variant="primary" onClick={() => setShowReportModal(true)}>Submit Task Report</Button>
                      <Button variant="warning" onClick={() => setShowIssueModal(true)}>Report Site Issue</Button>
                      <Button variant="success" onClick={() => setShowPhotoModal(true)}>Upload Site Photos</Button>
                    </div>
                  </div>

                  <Form.Group className="mb-3" controlId="projectDateSelector">
                    <Form.Label className="fw-semibold">Select Date</Form.Label>
                    <Form.Select value={selectedDate} onChange={handleDateChange}>
                      {availableDates.map((d) => (
                        <option key={d} value={d}>{new Date(d).toLocaleDateString()}</option>
                      ))}
                    </Form.Select>
                  </Form.Group>

                  <Row>
                    <Col md={12} className="mb-3">
                      <h6 className="mb-2">Task Reports</h6>
                      <div className="border rounded p-2">
                        {dailyItemsByProject[currentProject.id]?.[selectedDate]?.reports?.length ? (
                          <ul className="mb-0">
                            {dailyItemsByProject[currentProject.id][selectedDate].reports.map((r) => (
                              <li key={r.id}>
                                <span className="fw-semibold">{r.title}</span>
                                {r.details ? ` – ${r.details}` : ""}
                              </li>
                            ))}
                          </ul>
                        ) : (
                          <div className="text-muted">No reports for this date.</div>
                        )}
                      </div>
                    </Col>

                    <Col md={12} className="mb-3">
                      <h6 className="mb-2">Site Issues</h6>
                      <div className="border rounded p-2">
                        {dailyItemsByProject[currentProject.id]?.[selectedDate]?.issues?.length ? (
                          <ul className="mb-0">
                            {dailyItemsByProject[currentProject.id][selectedDate].issues.map((i) => (
                              <li key={i.id}>
                                <span className="fw-semibold">[{i.severity?.toUpperCase()}]</span> {i.title}
                                {i.details ? ` – ${i.details}` : ""}
                              </li>
                            ))}
                          </ul>
                        ) : (
                          <div className="text-muted">No issues for this date.</div>
                        )}
                      </div>
                    </Col>

                    <Col md={12}>
                      <h6 className="mb-2">Site Photos</h6>
                      <div className="border rounded p-2">
                        {dailyItemsByProject[currentProject.id]?.[selectedDate]?.photos?.length ? (
                          <ul className="mb-0">
                            {dailyItemsByProject[currentProject.id][selectedDate].photos.map((p) => (
                              <li key={p.id}>{p.name}</li>
                            ))}
                          </ul>
                        ) : (
                          <div className="text-muted">No photos for this date.</div>
                        )}
                      </div>
                    </Col>
                  </Row>
                </div>
              )}
            </Modal.Body>
          </Modal>

          {/* Submit Task Report Modal */}
          <Modal show={showReportModal} onHide={() => setShowReportModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Submit Task Report</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleReportSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Title</Form.Label>
                  <Form.Control
                    type="text"
                    value={reportForm.title}
                    onChange={(e) => setReportForm((f) => ({ ...f, title: e.target.value }))}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Details</Form.Label>
                  <Form.Control
                    as="textarea"
                    rows={3}
                    value={reportForm.details}
                    onChange={(e) => setReportForm((f) => ({ ...f, details: e.target.value }))}
                  />
                </Form.Group>
                <div className="d-flex justify-content-end gap-2">
                  <Button variant="secondary" onClick={() => setShowReportModal(false)}>Cancel</Button>
                  <Button variant="primary" type="submit">Submit</Button>
                </div>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Report Site Issue Modal */}
          <Modal show={showIssueModal} onHide={() => setShowIssueModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Report Site Issue</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleIssueSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Title</Form.Label>
                  <Form.Control
                    type="text"
                    value={issueForm.title}
                    onChange={(e) => setIssueForm((f) => ({ ...f, title: e.target.value }))}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Severity</Form.Label>
                  <Form.Select
                    value={issueForm.severity}
                    onChange={(e) => setIssueForm((f) => ({ ...f, severity: e.target.value }))}
                  >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                  </Form.Select>
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Details</Form.Label>
                  <Form.Control
                    as="textarea"
                    rows={3}
                    value={issueForm.details}
                    onChange={(e) => setIssueForm((f) => ({ ...f, details: e.target.value }))}
                  />
                </Form.Group>
                <div className="d-flex justify-content-end gap-2">
                  <Button variant="secondary" onClick={() => setShowIssueModal(false)}>Cancel</Button>
                  <Button variant="warning" type="submit">Submit Issue</Button>
                </div>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Upload Site Photos Modal */}
          <Modal show={showPhotoModal} onHide={() => setShowPhotoModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Upload Site Photos</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handlePhotoSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Photos</Form.Label>
                  <Form.Control
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={(e) => setPhotoFiles(Array.from(e.target.files || []))}
                    required
                  />
                </Form.Group>
                {photoFiles.length > 0 && (
                  <div className="small text-muted mb-2">{photoFiles.length} file(s) selected</div>
                )}
                <div className="d-flex justify-content-end gap-2">
                  <Button variant="secondary" onClick={() => setShowPhotoModal(false)}>Cancel</Button>
                  <Button variant="success" type="submit">Upload</Button>
                </div>
              </Form>
            </Modal.Body>
          </Modal>
        </Container>
      </main>
    </div>
  );
};

export default ProjectMonitoring;