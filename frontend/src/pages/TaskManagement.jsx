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
  const [users, setUsers] = useState([]);
  const [newTask, setNewTask] = useState({
    task_name: '',
    description: '',
    assigned_to: '',
    project_id: '',
    status: 'Pending',
    due_date: '',
    created_by: '',
  });
  const [newProject, setNewProject] = useState({
    name: '',
    description: '',
    start_date: '',
    end_date: '',
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
    fetchProjects();
    fetchTasks();
    fetchUsers();
  }, []);

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

  const fetchTasks = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/tasks', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      console.log('Fetched tasks:', data);
      if (data.success) {
        const sortedTasks = [...data.tasks].sort(
          (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
        setTasks(sortedTasks);
      }
    } catch (error) {
      console.error('Error fetching tasks:', error);
    }
  };

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

  const handleTaskSubmit = async (e) => {
    e.preventDefault();
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Authentication token not found. Please log in again.');
        return;
      }

      // Get current user for created_by field
      const userData = localStorage.getItem('user');
      const user = userData ? JSON.parse(userData) : null;
      
      const taskData = {
        ...newTask,
        created_by: user ? user.id : '',
      };

      const response = await fetch('http://localhost:8000/api/tasks', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(taskData),
      });

      // Check if response is redirect (HTML instead of JSON)
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        alert('Authentication failed. Please log in again.');
        return;
      }

      const data = await response.json();
      if (data.status === 'success') {
        setTasks([...tasks, data.task]);
        setNewTask({
          task_name: '',
          description: '',
          assigned_to: '',
          project_id: '',
          status: 'Pending',
          due_date: '',
          created_by: '',
        });
        setShowTaskModal(false);
        // Refresh tasks list
        fetchTasks();
        alert('Task created successfully!');
      } else {
        console.error('Error creating task:', data.message);
        alert('Failed to create task: ' + (data.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error adding task:', error);
      if (error.message.includes('Failed to fetch')) {
        alert('Authentication failed or server error. Please log in again.');
      } else {
        alert('Error creating task. Please try again.');
      }
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
      console.log(data);
      if (data.status === 'success' || data.success === true) {
        setNewProject({
          name: '',
          description: '',
          start_date: '',
          end_date: '',
          created_by: '',
          archived: false,
        });
        setShowProjectModal(false);
        fetchProjects();
      }
    } catch (error) {
      console.error('Error adding project:', error);
    }
  };

  const handleEditTaskSubmit = async (e) => {
    e.preventDefault();
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Authentication token not found. Please log in again.');
        return;
      }

      const response = await fetch(`http://localhost:8000/api/tasks/${editTask.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(editTask),
      });

      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        alert('Authentication failed. Please log in again.');
        return;
      }
  
      const data = await response.json();
      if (data.status === 'success') {
        setTasks(tasks.map((task) =>
          task.id === editTask.id ? data.task : task
        ));
        setShowEditTaskModal(false);
        alert('Task updated successfully!');
      } else {
        alert('Error: ' + (data.message || 'Failed to update task.'));
      }
    } catch (error) {
      console.error('Error editing task:', error);
      if (error.message.includes('Failed to fetch')) {
        alert('Authentication failed or server error. Please log in again.');
      } else {
        alert('Error editing task. Please try again.');
      }
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



  // Helper function to format status for display
  const formatStatus = (status) => {
    return status
      .split('_')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  // Helper function to get status badge color
  const getStatusBadgeColor = (status) => {
    switch (status) {
      case 'completed':
        return 'success';
      case 'in_progress':
        return 'primary';
      case 'on_hold':
        return 'warning';
      case 'cancelled':
        return 'danger';
      case 'pending':
      default:
        return 'secondary';
    }
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
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Archived</th>
                      </tr>
                    </thead>
                    <tbody>
                      {projects.map((project, index) => (
                        <tr key={project.id}>
                          <td>{index + 1}</td>
                          <td>{project.name}</td>
                          <td>{project.description}</td>
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
                        <th>Project</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Created</th>
                      </tr>
                    </thead>
                    <tbody>
                      {tasks.map((task, index) => (
                        <tr key={task.id}>
                          <td>{index + 1}</td>
                          <td>{task.task_name}</td>
                          <td>{task.description}</td>
                          <td>
                            {users.find(u => u.id === task.assigned_to)?.first_name} {users.find(u => u.id === task.assigned_to)?.last_name} ({users.find(u => u.id === task.assigned_to)?.username})
                          </td>
                          <td>
                            {projects.find(p => p.id === task.project_id)?.name || task.project_id}
                          </td>
                                                     <td>
                             {task.due_date
                               ? new Date(task.due_date).toISOString().split('T')[0]
                               : '-'}
                           </td>
                          <td>
                            <span className={`badge bg-${
                              task.status === 'Completed' ? 'success' :
                              task.status === 'In Progress' ? 'primary' :
                              task.status === 'On Hold' ? 'warning' :
                              task.status === 'Cancelled' ? 'danger' : 'secondary'
                            }`}>
                              {task.status}
                            </span>
                          </td>
                                                     <td>
                             {task.created_at
                               ? new Date(task.created_at).toISOString().split('T')[0]
                               : '-'}
                           </td>
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
              <Modal.Title>Create New Task</Modal.Title>
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
                    placeholder="Enter task name"
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
                    placeholder="Enter task description"
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Assigned To</Form.Label>
                  <Form.Select
                    name="assigned_to"
                    value={newTask.assigned_to}
                    onChange={handleTaskChange}
                    required
                  >
                    <option value="">Select an assignee</option>
                    {users.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.first_name} {user.last_name} ({user.username})
                      </option>
                    ))}
                  </Form.Select>
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Project</Form.Label>
                  <Form.Select
                    name="project_id"
                    value={newTask.project_id}
                    onChange={handleTaskChange}
                    required
                  >
                    <option value="">Select a project</option>
                    {projects.map((project) => (
                      <option key={project.id} value={project.id}>
                        {project.name}
                      </option>
                    ))}
                  </Form.Select>
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Due Date</Form.Label>
                  <Form.Control
                    type="date"
                    name="due_date"
                    value={newTask.due_date}
                    onChange={handleTaskChange}
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
                    <option value="On Hold">On Hold</option>
                    <option value="Cancelled">Cancelled</option>
                  </Form.Select>
                </Form.Group>
                                 <div className="d-flex gap-2 justify-content-end">
                   <Button variant="secondary" onClick={() => setShowTaskModal(false)}>
                     Cancel
                   </Button>
                   <Button variant="primary" type="submit">
                     Create Task
                   </Button>
                 </div>
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
                  <Form.Label>Start Date</Form.Label>
                  <Form.Control
                    type="date"
                    name="start_date"
                    value={newProject.start_date || ''}
                    onChange={handleProjectChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>End Date</Form.Label>
                  <Form.Control
                    type="date"
                    name="end_date"
                    value={newProject.end_date || ''}
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

                     <Modal show={showEditTaskModal} onHide={() => setShowEditTaskModal(false)} centered size="lg">
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
                          <td>
                            {users.find(u => u.id === task.assigned_to)?.first_name} {users.find(u => u.id === task.assigned_to)?.last_name} ({users.find(u => u.id === task.assigned_to)?.username})
                          </td>
                          <td>{projects.find(p => p.id === task.project_id)?.name || task.project_id}</td>
                          <td>
                            <span className={`badge bg-${getStatusBadgeColor(task.status)}`}>
                              {formatStatus(task.status)}
                            </span>
                          </td>
                          <td>
                          <Button
                            variant="primary"
                            size="sm"
                            onClick={() => {
                              setEditTask({ ...task });
                              setShowTaskSelection(false);
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
                    <Form.Select
                      name="assigned_to"
                      value={editTask.assigned_to || ''}
                      onChange={handleEditTaskChange}
                      required
                    >
                      <option value="">Select an assignee</option>
                      {users.map((user) => (
                        <option key={user.id} value={user.id}>
                          {user.first_name} {user.last_name} ({user.username})
                        </option>
                      ))}
                    </Form.Select>
                  </Form.Group>
                  <Form.Group className="mb-3">
                    <Form.Label>Project</Form.Label>
                    <Form.Select
                      name="project_id"
                      value={editTask.project_id || ''}
                      onChange={handleEditTaskChange}
                      required
                    >
                      <option value="">Select a project</option>
                      {projects.map((project) => (
                        <option key={project.id} value={project.id}>
                          {project.name}
                        </option>
                      ))}
                    </Form.Select>
                  </Form.Group>
                  <Form.Group className="mb-3">
                    <Form.Label>Due Date</Form.Label>
                    <Form.Control
                      type="date"
                      name="due_date"
                      value={editTask.due_date || ''}
                      onChange={handleEditTaskChange}
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
                      <option value="On Hold">On Hold</option>
                      <option value="Cancelled">Cancelled</option>
                    </Form.Select>
                  </Form.Group>
                  <div className="d-flex gap-2 justify-content-end">
                    <Button variant="secondary" onClick={() => setShowEditTaskModal(false)}>
                      Cancel
                    </Button>
                    <Button variant="primary" type="submit">
                      Save Changes
                    </Button>
                  </div>
                </Form>
              )}
            </Modal.Body>
          </Modal>

                     <Modal show={showArchiveTaskModal} onHide={() => setShowArchiveTaskModal(false)} centered size="lg">
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
                      <td>
                        {users.find(u => u.id === task.assigned_to)?.first_name} {users.find(u => u.id === task.assigned_to)?.last_name} ({users.find(u => u.id === task.assigned_to)?.username})
                      </td>
                      <td>{projects.find(p => p.id === task.project_id)?.name || task.project_id}</td>
                      <td>
                        <span className={`badge bg-${getStatusBadgeColor(task.status)}`}>
                          {formatStatus(task.status)}
                        </span>
                      </td>
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