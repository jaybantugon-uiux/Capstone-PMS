import React, { useEffect, useState } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Form, Modal } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import TaskAltIcon from '@mui/icons-material/TaskAlt';
import EditIcon from '@mui/icons-material/Edit';
import ArchiveIcon from '@mui/icons-material/Archive';
import '../css/Dashboard.css';

const TaskManagement = () => {
  const [userRole, setUserRole] = useState('');
  const [tasks, setTasks] = useState([]);
  const [projects, setProjects] = useState([]);
  const [newTask, setNewTask] = useState({
    task_name: '',
    description: '',
    assigned_to: '',
    project_id: '',
    status: 'Pending',
  });
  const [newProject, setNewProject] = useState({
    name: '',
    description: '',
    created_by: '',
    archived: false,
  });
  const [showTaskModal, setShowTaskModal] = useState(false);
  const [showProjectModal, setShowProjectModal] = useState(false);
  const [editTask, setEditTask] = useState({});
  const [showEditTaskModal, setShowEditTaskModal] = useState(false);
  const [showTaskSelection, setShowTaskSelection] = useState(false);
  const [showArchiveTaskModal, setShowArchiveTaskModal] = useState(false);

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }

    fetchTasks();
    fetchProjects();
  }, []);

  const fetchTasks = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/tasks', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      setTasks(data.tasks);
    } catch (error) {
      console.error('Error fetching tasks:', error);
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
      setProjects(data.activeProjects.concat(data.archivedProjects));
    } catch (error) {
      console.error('Error fetching projects:', error);
    }
  };

  const handleTaskSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch('http://localhost:8000/api/tasks', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(newTask),
      });
      const data = await response.json();
      if (data.status === 'success') {
        setTasks([...tasks, data.task]);
        setNewTask({
          task_name: '',
          description: '',
          assigned_to: '',
          project_id: '',
          status: 'Pending',
        });
        setShowTaskModal(false);
      }
    } catch (error) {
      console.error('Error adding task:', error);
    }
  };

  const handleProjectSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch('http://localhost:8000/api/projects', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(newProject),
      });
      const data = await response.json();
      if (data.status === 'success') {
        setProjects([...projects, data.project]);
        setNewProject({
          name: '',
          description: '',
          created_by: '',
          archived: false,
        });
        setShowProjectModal(false);
      }
    } catch (error) {
      console.error('Error adding project:', error);
    }
  };

  const handleEditTaskSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`http://localhost:8000/api/tasks/${editTask.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(editTask),
      });
      const data = await response.json();
      if (data.status === 'success') {
        setTasks(tasks.map((task) => (task.id === editTask.id ? data.task : task)));
        setShowEditTaskModal(false);
      }
    } catch (error) {
      console.error('Error editing task:', error);
    }
  };

  const handleArchiveTaskSubmit = async (taskId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/tasks/${taskId}/archive`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      if (data.status === 'success') {
        setTasks(tasks.map((task) => (task.id === taskId ? { ...task, archived: true } : task)));
        setShowArchiveTaskModal(false);
      }
    } catch (error) {
      console.error('Error archiving task:', error);
    }
  };

  const handleTaskChange = (e) => {
    const { name, value } = e.target;
    setNewTask({ ...newTask, [name]: value });
  };

  const handleProjectChange = (e) => {
    const { name, value } = e.target;
    setNewProject({ ...newProject, [name]: value });
  };

  const handleEditTaskChange = (e) => {
    const { name, value } = e.target;
    setEditTask({ ...editTask, [name]: value });
  };

  const handleTaskSelection = (task) => {
    setEditTask(task);
    setShowTaskSelection(false);
  };

  const roleLabelMap = {
    admin: 'Admin',
    emp: 'Employee',
    finance: 'Finance Admin',
    pm: 'Project Manager',
    sc: 'Site Coordinator',
    client: 'Client',
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
                  style={{ width: 'auto' }}
                  disabled
                >
                  <option>{roleLabelMap[userRole] || 'User'}</option>
                </Form.Select>
              </div>
            </Col>
          </Row>

          <Row className="mb-4">
            <Col className="d-flex justify-content-end gap-2">
              <Button
                variant="primary"
                className="create-project-modal"
                onClick={() => setShowProjectModal(true)}
              >
                <AddCircleOutlineIcon className="me-2" />
                <span className="d-none d-md-inline">Create Project</span>
              </Button>
              <Button
                variant="primary"
                className="create-task-modal"
                onClick={() => setShowTaskModal(true)}
              >
                <TaskAltIcon className="me-2" />
                <span className="d-none d-md-inline">Create Task</span>
              </Button>
              <Button
                variant="primary"
                className="edit-task-modal"
                onClick={() => {
                  setShowEditTaskModal(true);
                  setShowTaskSelection(true);
                }}
              >
                <EditIcon className="me-2" />
                <span className="d-none d-md-inline">Edit Task</span>
              </Button>
              <Button
                variant="primary"
                className="archive-task-modal"
                onClick={() => setShowArchiveTaskModal(true)}
              >
                <ArchiveIcon className="me-2" />
                <span className="d-none d-md-inline">Archive Task</span>
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
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th>Archived</th>
                      </tr>
                    </thead>
                    <tbody>
                      {projects.map((project, index) => (
                        <tr key={project.id}>
                          <td>{index + 1}</td>
                          <td>{project.name}</td>
                          <td>{project.description}</td>
                          <td>{project.created_by}</td>
                          <td>{project.archived ? 'Yes' : 'No'}</td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header>
                  <h5>Task List</h5>
                </Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Task Name</th>
                        <th>Description</th>
                        <th>Assigned To</th>
                        <th>Project ID</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {tasks.map((task, index) => (
                        <tr key={task.id}>
                          <td>{index + 1}</td>
                          <td>{task.task_name}</td>
                          <td>{task.description}</td>
                          <td>{task.assigned_to}</td>
                          <td>{task.project_id}</td>
                          <td>{task.status}</td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          <Modal show={showTaskModal} onHide={() => setShowTaskModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Add New Task</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleTaskSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Task Name</Form.Label>
                  <Form.Control
                    type="text"
                    name="task_name"
                    value={newTask.task_name}
                    onChange={handleTaskChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    as="textarea"
                    name="description"
                    value={newTask.description}
                    onChange={handleTaskChange}
                    rows={3}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Assigned To</Form.Label>
                  <Form.Control
                    type="text"
                    name="assigned_to"
                    value={newTask.assigned_to}
                    onChange={handleTaskChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Project ID</Form.Label>
                  <Form.Control
                    type="text"
                    name="project_id"
                    value={newTask.project_id}
                    onChange={handleTaskChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Status</Form.Label>
                  <Form.Select
                    name="status"
                    value={newTask.status}
                    onChange={handleTaskChange}
                  >
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                  </Form.Select>
                </Form.Group>
                <Button variant="primary" type="submit">
                  Add Task
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showProjectModal} onHide={() => setShowProjectModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Create New Project</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleProjectSubmit}>
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
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    as="textarea"
                    name="description"
                    value={newProject.description}
                    onChange={handleProjectChange}
                    rows={3}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Created By</Form.Label>
                  <Form.Control
                    type="text"
                    name="created_by"
                    value={newProject.created_by}
                    onChange={handleProjectChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Archived</Form.Label>
                  <Form.Check
                    type="checkbox"
                    name="archived"
                    checked={newProject.archived}
                    onChange={(e) =>
                      setNewProject({ ...newProject, archived: e.target.checked })
                    }
                  />
                </Form.Group>
                <Button variant="primary" type="submit">
                  Add Project
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showEditTaskModal} onHide={() => setShowEditTaskModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Edit Task</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              {showTaskSelection ? (
                <div>
                  <h5>Select a Task to Edit</h5>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Task Name</th>
                        <th>Description</th>
                        <th>Assigned To</th>
                        <th>Project ID</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      {tasks.map((task, index) => (
                        <tr key={task.id}>
                          <td>{index + 1}</td>
                          <td>{task.task_name}</td>
                          <td>{task.description}</td>
                          <td>{task.assigned_to}</td>
                          <td>{task.project_id}</td>
                          <td>{task.status}</td>
                          <td>
                            <Button
                              variant="primary"
                              size="sm"
                              onClick={() => {
                                handleTaskSelection(task);
                                setShowTaskSelection(false); // Switch to form view
                              }}
                            >
                              Edit
                            </Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </div>
              ) : (
                <Form onSubmit={handleEditTaskSubmit}>
                  <Form.Group className="mb-3">
                    <Form.Label>Task Name</Form.Label>
                    <Form.Control
                      type="text"
                      name="task_name"
                      value={editTask.task_name || ''}
                      onChange={handleEditTaskChange}
                      required
                    />
                  </Form.Group>
                  <Form.Group className="mb-3">
                    <Form.Label>Description</Form.Label>
                    <Form.Control
                      as="textarea"
                      name="description"
                      value={editTask.description || ''}
                      onChange={handleEditTaskChange}
                      rows={3}
                      required
                    />
                  </Form.Group>
                  <Form.Group className="mb-3">
                    <Form.Label>Assigned To</Form.Label>
                    <Form.Control
                      type="text"
                      name="assigned_to"
                      value={editTask.assigned_to || ''}
                      onChange={handleEditTaskChange}
                      required
                    />
                  </Form.Group>
                  <Form.Group className="mb-3">
                    <Form.Label>Project ID</Form.Label>
                    <Form.Control
                      type="text"
                      name="project_id"
                      value={editTask.project_id || ''}
                      onChange={handleEditTaskChange}
                      required
                    />
                  </Form.Group>
                  <Form.Group className="mb-3">
                    <Form.Label>Status</Form.Label>
                    <Form.Select
                      name="status"
                      value={editTask.status || ''}
                      onChange={handleEditTaskChange}
                    >
                      <option value="Pending">Pending</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Completed">Completed</option>
                    </Form.Select>
                  </Form.Group>
                  <Button variant="primary" type="submit">
                    Save Changes
                  </Button>
                </Form>
              )}
            </Modal.Body>
          </Modal>

          <Modal show={showArchiveTaskModal} onHide={() => setShowArchiveTaskModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Archive Task</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <h5>Select a Task to Archive</h5>
              <Table responsive bordered hover>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Assigned To</th>
                    <th>Project ID</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {tasks.map((task, index) => (
                    <tr key={task.id}>
                      <td>{index + 1}</td>
                      <td>{task.task_name}</td>
                      <td>{task.description}</td>
                      <td>{task.assigned_to}</td>
                      <td>{task.project_id}</td>
                      <td>{task.status}</td>
                      <td>
                        <Button
                          variant="danger"
                          size="sm"
                          onClick={() => handleArchiveTaskSubmit(task.id)}
                        >
                          Archive
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </Table>
            </Modal.Body>
          </Modal>
        </Container>
      </main>
    </div>
  );
};

export default TaskManagement;