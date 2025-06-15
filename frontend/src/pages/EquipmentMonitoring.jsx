import React, { useEffect, useState } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Form, Modal } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import EditIcon from '@mui/icons-material/Edit';
import ArchiveIcon from '@mui/icons-material/Archive';
import '../css/Dashboard.css';

const EquipmentMonitoring = () => {
  const [userRole, setUserRole] = useState('');
  const [equipment, setEquipment] = useState([]);
  const [newRequest, setNewRequest] = useState({
    name: '',
    quantity: 0,
    description: '',
  });
  const [editEquipment, setEditEquipment] = useState({});
  const [showRequestModal, setShowRequestModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showMaintenanceModal, setShowMaintenanceModal] = useState(false);

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }

    fetchEquipment();
  }, []);

  const fetchEquipment = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/equipment', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      setEquipment(data.equipment);
    } catch (error) {
      console.error('Error fetching equipment:', error);
    }
  };

  const handleRequestSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch('http://localhost:8000/api/equipment/request', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(newRequest),
      });
      const data = await response.json();
      if (data.status === 'success') {
        setEquipment([...equipment, data.equipment]);
        setNewRequest({ name: '', quantity: 0, description: '' });
        setShowRequestModal(false);
      }
    } catch (error) {
      console.error('Error requesting equipment:', error);
    }
  };

  const handleEditSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`http://localhost:8000/api/equipment/${editEquipment.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(editEquipment),
      });
      const data = await response.json();
      if (data.status === 'success') {
        setEquipment(
          equipment.map((item) => (item.id === editEquipment.id ? data.equipment : item))
        );
        setShowEditModal(false);
      }
    } catch (error) {
      console.error('Error updating equipment:', error);
    }
  };

  const handleMaintenanceSubmit = async (equipmentId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/equipment/${equipmentId}/maintenance`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      if (data.status === 'success') {
        setShowMaintenanceModal(false);
      }
    } catch (error) {
      console.error('Error scheduling maintenance:', error);
    }
  };

  const handleRequestChange = (e) => {
    const { name, value } = e.target;
    setNewRequest({ ...newRequest, [name]: value });
  };

  const handleEditEquipmentChange = (e) => {
    const { name, value } = e.target;
    setEditEquipment({ ...editEquipment, [name]: value });
  };

  const roleLabelMap = {
    admin: 'Admin',
    coordinator: 'Site Coordinator',
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
                className="request-equipment-modal"
                onClick={() => setShowRequestModal(true)}
              >
                <AddCircleOutlineIcon className="me-2" />
                <span className="d-none d-md-inline">Request Equipment</span>
              </Button>
              <Button
                variant="primary"
                className="edit-equipment-modal"
                onClick={() => setShowEditModal(true)}
              >
                <EditIcon className="me-2" />
                <span className="d-none d-md-inline">Update Equipment</span>
              </Button>
              <Button
                variant="warning"
                className="maintenance-equipment-modal"
                onClick={() => setShowMaintenanceModal(true)}
              >
                <ArchiveIcon className="me-2" />
                <span className="d-none d-md-inline">Schedule Maintenance</span>
              </Button>
            </Col>
          </Row>

          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header>
                  <h5>Equipment List</h5>
                </Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Description</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      {equipment.map((item, index) => (
                        <tr key={item.id}>
                          <td>{index + 1}</td>
                          <td>{item.name}</td>
                          <td>{item.quantity}</td>
                          <td>{item.description}</td>
                          <td>
                            <Button
                              variant="warning"
                              size="sm"
                              onClick={() => handleMaintenanceSubmit(item.id)}
                            >
                              Maintenance
                            </Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          {/* Request Equipment Modal */}
          <Modal show={showRequestModal} onHide={() => setShowRequestModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Request Equipment</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleRequestSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Equipment Name</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    value={newRequest.name}
                    onChange={handleRequestChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Quantity</Form.Label>
                  <Form.Control
                    type="number"
                    name="quantity"
                    value={newRequest.quantity}
                    onChange={handleRequestChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    as="textarea"
                    name="description"
                    value={newRequest.description}
                    onChange={handleRequestChange}
                    rows={3}
                  />
                </Form.Group>
                <Button variant="primary" type="submit">
                  Submit Request
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Update Equipment Modal */}
          <Modal show={showEditModal} onHide={() => setShowEditModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Update Equipment</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleEditSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Equipment Name</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    value={editEquipment.name || ''}
                    onChange={handleEditEquipmentChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Quantity</Form.Label>
                  <Form.Control
                    type="number"
                    name="quantity"
                    value={editEquipment.quantity || ''}
                    onChange={handleEditEquipmentChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    as="textarea"
                    name="description"
                    value={editEquipment.description || ''}
                    onChange={handleEditEquipmentChange}
                    rows={3}
                  />
                </Form.Group>
                <Button variant="primary" type="submit">
                  Save Changes
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Schedule Maintenance Modal */}
          <Modal show={showMaintenanceModal} onHide={() => setShowMaintenanceModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Schedule Maintenance</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <h5>Select Equipment for Maintenance</h5>
              <Table responsive bordered hover>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Description</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {equipment.map((item, index) => (
                    <tr key={item.id}>
                      <td>{index + 1}</td>
                      <td>{item.name}</td>
                      <td>{item.quantity}</td>
                      <td>{item.description}</td>
                      <td>
                        <Button
                          variant="warning"
                          size="sm"
                          onClick={() => handleMaintenanceSubmit(item.id)}
                        >
                          Schedule
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

export default EquipmentMonitoring;