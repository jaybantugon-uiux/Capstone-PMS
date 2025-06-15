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
                          <td>{new Date(project.startDate).toLocaleDateString()}</td>
                          <td>{new Date(project.endDate).toLocaleDateString()}</td>
                          <td>{project.status}</td>
                          <td>
                            <Button variant="info" size="sm">View</Button>
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
        </Container>
      </main>
    </div>
  );
};

export default ProjectMonitoring;