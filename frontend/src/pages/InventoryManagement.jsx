import React, { useEffect, useState } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Form, Modal } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import EditIcon from '@mui/icons-material/Edit';
import ArchiveIcon from '@mui/icons-material/Archive';
import '../css/Dashboard.css';

const InventoryManagement = () => {
  const [userRole, setUserRole] = useState('');
  const [equipment, setEquipment] = useState([]);
  const [newEquipment, setNewEquipment] = useState({ name: '', quantity: 0, description: '' });
  const [editEquipment, setEditEquipment] = useState({});
  const [showRestockModal, setShowRestockModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showArchiveModal, setShowArchiveModal] = useState(false);

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
      if (data.status === 'success') {
        setEquipment(data.equipment);
      }
    } catch (error) {
      console.error('Error fetching equipment:', error);
    }
  };

  const handleRestockSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch('http://localhost:8000/api/equipment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(newEquipment),
      });
      const data = await response.json();
      if (data.status === 'success') {
        fetchEquipment(); // refresh list
        setNewEquipment({ name: '', quantity: 0, description: '' });
        setShowRestockModal(false);
      }
    } catch (error) {
      console.error('Error restocking equipment:', error);
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
        fetchEquipment(); // refresh list
        setShowEditModal(false);
      }
    } catch (error) {
      console.error('Error updating equipment:', error);
    }
  };

  const handleArchiveSubmit = async (equipmentId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/equipment/${equipmentId}/archive`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      if (data.status === 'success') {
        fetchEquipment(); // refresh list
        setShowArchiveModal(false);
      }
    } catch (error) {
      console.error('Error archiving equipment:', error);
    }
  };

  const handleEquipmentChange = (e) => {
    const { name, value } = e.target;
    setNewEquipment({ ...newEquipment, [name]: value });
  };

  const handleEditEquipmentChange = (e) => {
    const { name, value } = e.target;
    setEditEquipment({ ...editEquipment, [name]: value });
  };

  const roleLabelMap = {
    admin: 'Admin',
    emp: 'Employee',
    inventory: 'Inventory Manager',
  };

  return (
    <div className="dashboard-layout">
      <main className="dashboard-main">
        <Container fluid className="h-100 py-4 px-4">
          {/* Header */}
          <Row className="mb-4">
            <Col className="d-flex align-items-center justify-content-between">
              <div className="dashboard-sidebar-wrapper">
                <Sidebar className="dashboard-sidebar" />
              </div>
              <div className="d-flex align-items-center gap-2">
                <PersonCircle size={40} />
                <Form.Select size="sm" className="border-0 bg-transparent" style={{ width: 'auto' }} disabled>
                  <option>{roleLabelMap[userRole] || 'User'}</option>
                </Form.Select>
              </div>
            </Col>
          </Row>

          {/* Actions */}
          <Row className="mb-4">
            <Col className="d-flex justify-content-end gap-2">
              <Button variant="primary" onClick={() => setShowRestockModal(true)}>
                <AddCircleOutlineIcon className="me-2" /> Restock Equipment
              </Button>
              <Button variant="primary" onClick={() => setShowEditModal(true)}>
                <EditIcon className="me-2" /> Edit Equipment
              </Button>
              <Button variant="danger" onClick={() => setShowArchiveModal(true)}>
                <ArchiveIcon className="me-2" /> Archive Equipment
              </Button>
            </Col>
          </Row>

          {/* Equipment Table */}
          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header><h5>Equipment List</h5></Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Description</th>
                        <th>Archived</th>
                      </tr>
                    </thead>
                    <tbody>
                      {equipment.map((item, index) => (
                        <tr key={item.id}>
                          <td>{index + 1}</td>
                          <td>{item.name}</td>
                          <td>{item.quantity}</td>
                          <td>{item.description}</td>
                          <td>{item.archived ? 'Yes' : 'No'}</td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          {/* Restock Modal */}
          <Modal show={showRestockModal} onHide={() => setShowRestockModal(false)} centered>
            <Modal.Header closeButton><Modal.Title>Restock Equipment</Modal.Title></Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleRestockSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Equipment Name</Form.Label>
                  <Form.Control type="text" name="name" value={newEquipment.name} onChange={handleEquipmentChange} required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Quantity</Form.Label>
                  <Form.Control type="number" name="quantity" value={newEquipment.quantity} onChange={handleEquipmentChange} required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control as="textarea" name="description" value={newEquipment.description} onChange={handleEquipmentChange} rows={3} />
                </Form.Group>
                <Button variant="primary" type="submit">Restock</Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Edit Modal */}
          <Modal show={showEditModal} onHide={() => setShowEditModal(false)} centered>
            <Modal.Header closeButton><Modal.Title>Update Equipment</Modal.Title></Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleEditSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Equipment Name</Form.Label>
                  <Form.Control type="text" name="name" value={editEquipment.name || ''} onChange={handleEditEquipmentChange} required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Quantity</Form.Label>
                  <Form.Control type="number" name="quantity" value={editEquipment.quantity || ''} onChange={handleEditEquipmentChange} required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control as="textarea" name="description" value={editEquipment.description || ''} onChange={handleEditEquipmentChange} rows={3} />
                </Form.Group>
                <Button variant="primary" type="submit">Save Changes</Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Archive Modal */}
          <Modal show={showArchiveModal} onHide={() => setShowArchiveModal(false)} centered>
            <Modal.Header closeButton><Modal.Title>Archive Equipment</Modal.Title></Modal.Header>
            <Modal.Body>
              <h5>Select Equipment to Archive</h5>
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
                        <Button variant="danger" size="sm" onClick={() => handleArchiveSubmit(item.id)}>
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

export default InventoryManagement;
