import React, { useState, useEffect } from "react";
import dayjs from 'dayjs';
import Sidebar from "../components/Sidebar";
import { Container, Row, Col, Card, Button, Table, Modal, Form } from "react-bootstrap";
import { PersonCircle } from "react-bootstrap-icons";
import AddCircleOutlineIcon from "@mui/icons-material/AddCircleOutline";
import { FaClipboardList, FaExclamationTriangle, FaCamera } from "react-icons/fa";
import ArchiveIcon from "@mui/icons-material/Archive";
import RestoreIcon from "@mui/icons-material/Restore";
import SearchIcon from "@mui/icons-material/Search";
import FilterListIcon from "@mui/icons-material/FilterList";
import "../css/Dashboard.css";

const ProjectMonitoring = () => {
  const [userRole, setUserRole] = useState("");
  const [users, setUsers] = useState([]);
  const [projects, setProjects] = useState([]);
  const [showArchiveModal, setShowArchiveModal] = useState(false);
  const [activeProjects, setActiveProjects] = useState([]);
  const [archivedProjects, setArchivedProjects] = useState([]);
  const [showArchiveProjectModal, setShowArchiveProjectModal] = useState(false);

  const [showSearchModal, setShowSearchModal] = useState(false);
  const [showFilterModal, setShowFilterModal] = useState(false);

  const [showViewModal, setShowViewModal] = useState(false);
  const [showSiteIssueModal, setShowSiteIssueModal] = useState(false);
  const [showReportTaskModal, setShowReportTaskModal] = useState(false);
  const [taskReportForm, setTaskReportForm] = useState({
    task_title: "",
    description: "",
    status: "open",
  });

  const [siteIssueForm, setSiteIssueForm] = useState({
    issue_title: "",
    description: "",
    status: "open",
  });

  const [taskReports, setTaskReports] = useState([]);

  const [selectedProject, setSelectedProject] = useState(null);
  const [selectedDate, setSelectedDate] = useState("");
  const [availableDates, setAvailableDates] = useState([]);
  const [issues, setIssues] = useState([]);
  const [photos, setPhotos] = useState([]);

  useEffect(() => {
    const userData = localStorage.getItem("user");
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }
    fetchUsers();
    fetchProjects();
  }, []);

  useEffect(() => {
    if (showArchiveProjectModal) {
      fetchActiveProjects();
      fetchArchivedProjects();
    }
  }, [showArchiveProjectModal]);
  

  useEffect(() => {
    if (selectedProject?.id && selectedDate) {
      fetchTaskReports(selectedProject.id, selectedDate);
    }
  }, [selectedProject, selectedDate]);

  useEffect(() => {
    if (selectedProject?.id && selectedDate) {
      fetchSiteIssues(selectedProject.id, selectedDate);
    }
  }, [selectedProject, selectedDate]);

  const fetchUsers = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/users', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      console.log('Fetched users:', data);
      if (data.success) {
        setUsers(data.users);
      }
    } catch (error) {
      console.error('Error fetching users:', error);
    }
  };

  const fetchProjects = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/projects', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      console.log('Fetched projects:', data);
      if (data.success) {
        const sortedProjects = [...data.projects].sort(
          (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
        setProjects(sortedProjects);
      }
    } catch (error) {
      console.error('Error fetching projects:', error);
    }
  };

  const fetchActiveProjects = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('http://localhost:8000/api/projects', {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
  
      const data = await response.json();
  
      if (data.success) {
        setActiveProjects(data.projects || []);
      } else {
        console.error('Failed to fetch active projects:', data.message);
      }
    } catch (error) {
      console.error('Error fetching active projects:', error);
    }
  };
  
  const fetchArchivedProjects = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('http://localhost:8000/api/projects/archived', {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
  
      const data = await response.json();
  
      if (data.success) {
        setArchivedProjects(data.projects || []);
      } else {
        console.error('Failed to fetch archived projects:', data.message);
      }
    } catch (error) {
      console.error('Error fetching archived projects:', error);
    }
  };

  const handleArchiveProject = async (projectId) => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`http://localhost:8000/api/projects/${projectId}/archive`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
  
      const data = await response.json();
  
      if (data.status === 'success' || data.status === 'info') {
        await fetchActiveProjects();
        await fetchArchivedProjects();
      }
    } catch (error) {
      console.error('Error archiving project:', error);
    }
  };
  
  const handleUnarchiveProject = async (projectId) => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`http://localhost:8000/api/projects/${projectId}/unarchive`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
  
      const data = await response.json();
  
      if (data.status === 'success' || data.status === 'info') {
        await fetchActiveProjects();
        await fetchArchivedProjects();
      }
    } catch (error) {
      console.error('Error unarchiving project:', error);
    }
  };

  const handleView = (project) => {
    setSelectedProject(project);
    setIssues([]);
    setPhotos([]);
    setSelectedDate("");
  
    if (project.start_date && project.end_date) {
      const dates = generateDateRange(project.start_date, project.end_date);
      setAvailableDates(dates);
    }
  
    setShowViewModal(true);
  };

  const handleReportChange = (e) => {
    const { name, value } = e.target;
    setSiteIssueForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };
  
  
  const handleReportTaskChange = (e) => {
    const { name, value } = e.target;
    setTaskReportForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const fetchTaskReports = async (projectId, selectedDate) => {
    try {
      const token = localStorage.getItem("token");
      const formattedDate = dayjs(selectedDate).format("YYYY-MM-DD");
  
      const response = await fetch("http://localhost:8000/api/taskReport", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          project_id: projectId,
          report_date: formattedDate,
        }),
      });
  
      const data = await response.json();
  
      if (response.ok && data.success) {
        console.log("Fetched task reports:", data.reports);
        setTaskReports(data.reports || []);
      } else {
        console.error("Failed to fetch task reports:", data.message || data);
      }
    } catch (error) {
      console.error("Unexpected error fetching task reports:", error);
    }
  };
  
  const handleReportTaskSubmit = async (e) => {
    e.preventDefault();
  
    try {
      const response = await fetch("http://localhost:8000/api/submitReport", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          project_id: selectedProject.id,
          task_title: taskReportForm.task_title,
          description: taskReportForm.description,
          status: taskReportForm.status,
          reported_at: dayjs(selectedDate).hour(dayjs().hour()).minute(dayjs().minute()).second(dayjs().second()).toISOString()
        }),
      });
  
      const data = await response.json();
      console.log("Submitted task report:", data);
  
      if (response.ok && data.success) {
        await fetchTaskReports(selectedProject.id, selectedDate);
  
        setTaskReportForm({
          task_title: "",
          description: "",
          status: "open",
        });
  
        setShowReportTaskModal(false);
      } else {
        console.error("Submission failed:", data.message || data);
      }
    } catch (error) {
      console.error("Unexpected error submitting task report:", error);
    }
  };
  

  const fetchSiteIssues = async (projectId, selectedDate) => {
  
    try {
      const token = localStorage.getItem("token");
      const formattedDate = dayjs(selectedDate).format("YYYY-MM-DD");
  
      const response = await fetch("http://localhost:8000/api/siteIssue", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          project_id: projectId,
          date: formattedDate,
        }),
      });
  
      const data = await response.json();
  
      if (response.ok && data.success) {
        console.log("Fetched site issues:", data.issues);
  
        const sortedIssues = (data.issues || []).sort(
          (a, b) => new Date(b.reported_at) - new Date(a.reported_at)
        );
  
        setIssues(sortedIssues);
      } else {
        console.error("Failed to fetch site issues:", data.message || data);
      }
    } catch (error) {
      console.error("Unexpected error fetching site issues:", error);
    }
  };
  
  const handleSiteIssueSubmit = async (e) => {
    e.preventDefault();
  
    try {
      const response = await fetch("http://localhost:8000/api/reportIssue", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          project_id: selectedProject.id,
          issue_title: siteIssueForm.issue_title,
          description: siteIssueForm.description,
          status: siteIssueForm.status,
          reported_at: dayjs(selectedDate).hour(dayjs().hour()).minute(dayjs().minute()).second(dayjs().second()).toISOString()
        }),
      });
  
      const data = await response.json();
      console.log("Submitted site issue:", data);
  
      if (response.ok) {
        await fetchSiteIssues(selectedProject.id, selectedDate);
  
        setSiteIssueForm({
          issue_title: "",
          description: "",
          status: "open",
        });
  
        setShowSiteIssueModal(false);
      } else {
        console.error("Submission failed:", data.message || data);
      }
    } catch (error) {
      console.error("Unexpected error:", error);
    }
  };

  const getStatusBadgeColor = (status) => {
    if (!status || typeof status !== 'string') return 'secondary'; // fallback badge
    switch (status.toLowerCase()) {
      case 'ongoing':
        return 'primary';
      case 'completed':
        return 'success';
      case 'delayed':
        return 'warning';
      case 'cancelled':
        return 'danger';
      default:
        return 'secondary';
    }
  };
  

  const generateDateRange = (start, end) => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const dates = [];
  
    while (startDate <= endDate) {
      dates.push(new Date(startDate).toISOString().split("T")[0]);
      startDate.setDate(startDate.getDate() + 1);
    }
  
    return dates.reverse();
  };
  
  const handleDateChange = async (e) => {
    const date = e.target.value;
    setSelectedDate(date);
      
  };

  const resolveProjectStatus = (project) => {
    const now = new Date();
    const start = project.start_date ? new Date(project.start_date) : null;
    const end = project.end_date ? new Date(project.end_date) : null;
  
    if (!start || !end) return 'No Schedule';
  
    if (now >= start && now <= end) return 'In Progress';
    if (now > end) return 'Completed';
    if (now < start) return 'Upcoming';
  
    return 'Unknown';
  };

  // Close modal
  const handleClose = () => {
    setShowViewModal(false);
    setSelectedProject(null);
    setSelectedDate("");
    setIssues([]);
    setPhotos([]);
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
            <Button variant="danger" onClick={() => setShowArchiveProjectModal(true)}>
              <ArchiveIcon className="me-2" />
              <span className="d-none d-md-inline">Archive Project</span>
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
                          <td>
                            {project.start_date
                              ? new Date(project.start_date).toISOString().split('T')[0]
                              : ''}
                          </td>
                          <td>
                            {project.end_date
                              ? new Date(project.end_date).toISOString().split('T')[0]
                              : ''}
                          </td>
                          <td>{resolveProjectStatus(project)}</td>
                          <td>
                            <Button
                              variant="info"
                              size="sm"
                              onClick={() => handleView(project)}
                            >
                              View
                            </Button>
                            <Button variant="primary" size="sm" className="mx-1">Edit</Button>
                            <Button variant="danger" size="sm" onClick={() => handleArchiveProject(project.id)}>Archive</Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          <Modal show={showArchiveProjectModal} onHide={() => setShowArchiveProjectModal(false)} centered size="xl">
            <Modal.Header closeButton>
              <Modal.Title>Manage Projects</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              {/* Active Projects */}
              <h5 className="mt-4 mb-3">Active Projects</h5>
              <Table responsive bordered hover>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Project Name</th>
                    <th>Description</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {activeProjects.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="text-center">No active projects found.</td>
                    </tr>
                  ) : (
                    activeProjects
                      .filter(p => !archivedProjects.some(a => a.id === p.id)) // prevent overlap
                      .map((project, index) => (
                        <tr key={`active-${project.id}`}>
                          <td>{index + 1}</td>
                          <td>{project.name}</td>
                          <td>{project.description}</td>
                          <td>{project.start_date}</td>
                          <td>{project.end_date}</td>
                          <td>
                            <span className={`badge bg-${getStatusBadgeColor(project.status || 'unknown')}`}>
                              {project.status || 'Unknown'}
                            </span>
                          </td>
                          <td>
                            <Button variant="danger" size="sm" onClick={() => handleArchiveProject(project.id)}>
                              Archive
                            </Button>
                          </td>
                        </tr>
                      ))
                  )}
                </tbody>
              </Table>

              {/* Archived Projects */}
              <h5 className="mt-4 mb-3">Archived Projects</h5>
              <Table responsive bordered hover>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Project Name</th>
                    <th>Description</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {archivedProjects.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="text-center">No archived projects found.</td>
                    </tr>
                  ) : (
                    archivedProjects.map((project, index) => (
                      <tr key={`archived-${project.id}`}>
                        <td>{index + 1}</td>
                        <td>{project.name}</td>
                        <td>{project.description}</td>
                        <td>{project.start_date}</td>
                        <td>{project.end_date}</td>
                        <td>
                          <span className={`badge bg-${getStatusBadgeColor(project.status || 'unknown')}`}>
                            {project.status || 'Unknown'}
                          </span>
                        </td>
                        <td>
                          <Button variant="success" size="sm" onClick={() => handleUnarchiveProject(project.id)}>
                            Unarchive
                          </Button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </Table>
            </Modal.Body>
          </Modal>


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

          <Modal show={showViewModal} onHide={() => setShowViewModal(false)} size="lg" centered>
            <Modal.Header closeButton className="bg-primary text-white">
              <Modal.Title>Project Details: {selectedProject?.name}</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              {/* Date + Buttons Row */}
              <Row className="align-items-center mb-3">
                <Col xs={12} md={4} className="text-center">
                <Form.Select value={selectedDate} onChange={handleDateChange}>
                  <option value="">-- Choose Date --</option>
                  {availableDates.map((date, idx) => (
                    <option key={idx} value={date}>
                      {new Date(date).toLocaleDateString()}
                    </option>
                  ))}
                </Form.Select>
                </Col>
                <Col xs={12} md={8} className="text-md-end text-center mt-2 mt-md-0">
                  <Button variant="success" className="me-2" onClick={() => setShowReportTaskModal(true)}>
                    <FaClipboardList className="me-md-2" />
                    <span className="d-none d-md-inline">Submit Task Report</span>
                  </Button>

                  <Button variant="danger" className="me-2" onClick={() => setShowSiteIssueModal(true)}>
                    <FaExclamationTriangle className="me-md-2" />
                    <span className="d-none d-md-inline">Report Site Issues</span>
                  </Button>
                  <Button variant="info">
                    <FaCamera className="me-md-2" />
                    <span className="d-none d-md-inline">Upload Site Photos</span>
                  </Button>
                </Col>
              </Row>

              {/* Details Table in Columns */}
              <Table bordered responsive>
                <thead>
                  <tr>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Workers</th>
                    <th>Cost</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{selectedProject?.client || ""}</td>
                    <td>{selectedProject?.type || ""}</td>
                    <td>{selectedProject?.workers || ""}</td>
                    <td>{selectedProject?.cost || ""}</td>
                    <td>{selectedProject?.start_date ? new Date(selectedProject.start_date).toLocaleDateString() : ""}</td>
                    <td>{selectedProject?.end_date ? new Date(selectedProject.end_date).toLocaleDateString() : ""}</td>
                    <td>{selectedProject ? resolveProjectStatus(selectedProject) : ""}</td>
                  </tr>
                </tbody>
              </Table>

              {/* Report Task Section */}
              <h5 className="mt-4">Task Reports</h5>
              <Table bordered responsive>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Report Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                  </tr>
                </thead>
                <tbody>
                  {taskReports.length > 0 ? (
                    taskReports.map((task, index) => (
                      <tr key={task.id || index}>
                        <td>{index + 1}</td>
                        <td>{task.task_title || '—'}</td>
                        <td>{task.description || '—'}</td>
                        <td>{task.status || '—'}</td>
                        <td>{task.reported_at.slice(11, 16)} {Number(task.reported_at.slice(11, 13)) < 12 ? 'AM' : 'PM'}</td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="5" className="text-muted text-center">
                        No task reports submitted for this date.
                      </td>
                    </tr>
                  )}
                </tbody>
              </Table>

              {/* Site Issues Section */}
              <h5 className="mt-4">Site Issues</h5>
              <Table bordered responsive>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Issue</th>
                    <th>Description</th>
                    <th>Timestamp</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {issues.length > 0 ? (
                    issues.map((issue, index) => (
                      <tr key={issue.id || index}>
                        <td>{index + 1}</td>
                        <td>{issue.issue_title || '—'}</td>
                        <td>{issue.description || '—'}</td>
                        <td>{issue.reported_at.slice(11, 16)} {Number(issue.reported_at.slice(11, 13)) < 12 ? 'AM' : 'PM'}</td>
                        <td>{issue.status || '—'}</td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="5" className="text-muted text-center">
                        No site issues reported for this date.
                      </td>
                    </tr>
                  )}
                </tbody>
              </Table>


              {/* Site Photos Section */}
              <h5 className="mt-4">Site Photos</h5>
              <Row>
                {photos && photos.length > 0 ? (
                  photos.map((photo, idx) => (
                    <Col xs={6} md={4} lg={3} key={idx} className="mb-3">
                      <Image src={photo.url} thumbnail fluid />
                    </Col>
                  ))
                ) : (
                  <p className="text-muted">No photos available for this date.</p>
                )}
              </Row>

            </Modal.Body>
            <Modal.Footer>
              <Button variant="secondary" onClick={handleClose} className="w-100">
                Close
              </Button>
            </Modal.Footer>
          </Modal>

          <Modal show={showReportTaskModal} onHide={() => setShowReportTaskModal(false)} centered>
            <Modal.Header closeButton className="bg-primary text-white">
              <Modal.Title>Submit Task Report</Modal.Title>
            </Modal.Header>
            <Modal.Body>
            <Form onSubmit={handleReportTaskSubmit}>
              <Form.Group className="mb-3" controlId="reportTitle">
                <Form.Label>Task Title</Form.Label>
                <Form.Control
                  type="text"
                  name="task_title"
                  value={taskReportForm.task_title || ""}
                  onChange={handleReportTaskChange}
                  placeholder="Enter task title"
                  required
                />
              </Form.Group>


                <Form.Group className="mb-3" controlId="taskDescription">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    as="textarea"
                    name="description"
                    value={taskReportForm.description || ""}
                    onChange={handleReportTaskChange}
                    rows={3}
                    placeholder="Describe the task"
                    required
                  />
                </Form.Group>

                <Form.Group className="mb-3" controlId="taskStatus">
                  <Form.Label>Status</Form.Label>
                  <Form.Select
                    name="status"
                    value={taskReportForm.status || ""}
                    onChange={handleReportTaskChange}
                    required
                  >
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                    <option value="escalated">Escalated</option>
                  </Form.Select>
                </Form.Group>

                <div className="text-end">
                  <Button variant="primary" type="submit">
                    Submit Task
                  </Button>
                </div>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showSiteIssueModal} onHide={() => setShowSiteIssueModal(false)} centered>
            <Modal.Header closeButton className="bg-danger text-white">
              <Modal.Title>Submit Site Issue</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleSiteIssueSubmit}>
                <Form.Group className="mb-3" controlId="issueTitle">
                <Form.Label>Issue Title</Form.Label>
                  <Form.Control
                    type="text"
                    name="issue_title"
                    value={siteIssueForm.issue_title || ""}
                    onChange={handleReportChange}
                    placeholder="Enter issue title"
                    required
                  />
                </Form.Group>

                <Form.Group className="mb-3" controlId="issueDescription">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    as="textarea"
                    name="description"
                    value={siteIssueForm.description || ""}
                    onChange={handleReportChange}
                    rows={3}
                    placeholder="Describe the issue"
                    required
                  />
                </Form.Group>

                <Form.Group className="mb-3" controlId="dateReported">
                  <Form.Label>Timestamp</Form.Label>
                  <Form.Control
                    type="text"
                    value={new Date().toLocaleDateString()}
                    readOnly
                    plaintext
                  />
                </Form.Group>


                <Form.Group className="mb-3" controlId="status">
                  <Form.Label>Status</Form.Label>
                  <Form.Select
                    name="status"
                    value={siteIssueForm.status || ""}
                    onChange={handleReportChange}
                    required
                  >
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                    <option value="escalated">Escalated</option>
                  </Form.Select>
                </Form.Group>

                <div className="text-end">
                  <Button variant="primary" type="submit">
                    Submit Report
                  </Button>
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