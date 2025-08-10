import React, { useEffect, useState } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Form, Modal } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import RestockIcon from '@mui/icons-material/Inventory2';
import EditIcon from '@mui/icons-material/Edit';
import ArchiveIcon from '@mui/icons-material/Archive';
import '../css/Dashboard.css';

const InventoryManagement = () => {
  const [userRole, setUserRole] = useState('');
  const [equipment, setEquipment] = useState([]);
  const [archivedEquipment, setArchivedEquipment] = useState([]);
  const [newEquipment, setNewEquipment] = useState({
    name: '',
    quantity: 0,
    description: '',
  });
  
  const [editEquipment, setEditEquipment] = useState({ id: '', name: '', quantity: 0, description: '' });
  const [restockData, setRestockData] = useState({ equipmentId: '', amount: 0, note: '' });
  const [showAddModal, setShowAddModal] = useState(false);
  const [showRestockModal, setShowRestockModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showArchiveEquipmentModal, setShowArchiveEquipmentModal] = useState(false);

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }

    fetchEquipment();
    fetchArchivedEquipment();
  }, []);

  const fetchEquipment = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/equipment', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
  
      const data = await response.json();
      console.log('Fetched equipment:', data);
  
      if (response.ok && Array.isArray(data.equipment)) {
        const sortedEquipment = [...data.equipment].sort(
          (a, b) => b.quantity - a.quantity
        );
  
        setEquipment(sortedEquipment);
      } else {
        console.log('Fetch failed:', data);
      }
    } catch (error) {
      console.error('Error fetching equipment:', error);
    }
  };
  
  
  const fetchArchivedEquipment = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('http://localhost:8000/api/equipment/archived', {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
  
      const data = await response.json();
  
      if (data.status === 'success') {
        setArchivedEquipment(data.equipment);
      } else {
        console.error('Failed to fetch archived equipment:', data.message || data);
      }
    } catch (error) {
      console.error('Error fetching archived equipment:', error);
    }
  };
  

  const handleAddSubmit = async (e) => {
    e.preventDefault();
  
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('http://localhost:8000/api/equipment', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(newEquipment),
      });
  
      const data = await response.json();
  
      if (data.status === 'success') {
        console.log('Equipment added:', data.equipment);
        setEquipment(prev => [...prev, data.equipment]);
        setNewEquipment({ name: '', quantity: 0, description: '' });
        setShowAddModal(false);
        await fetchEquipment();
      } else {
        console.error('Add failed:', data.message);
      }          
    } catch (error) {
      console.error('Error adding equipment:', error);
    }
  };

  const handleRestockSubmit = async (e) => {
    e.preventDefault();
  
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`http://localhost:8000/api/equipment/${restockData.equipmentId}/restock`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          amount: restockData.amount,
          note: restockData.note,
        }),
      });
  
      const data = await response.json();
  
      if (data.status === 'success') {
        console.log('Restock successful:', data.equipment);
        await fetchEquipment();
        setRestockData({ equipmentId: '', amount: 0, note: '' });
        setShowRestockModal(false);
      } else {
        console.error('Restock failed:', data.message);
      }
    } catch (error) {
      console.error('Error during restock:', error);
    }
  };

  const handleEditSubmit = async (e) => {
    e.preventDefault();
  
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`http://localhost:8000/api/equipment/${editEquipment.id}`, {
        method: 'PUT',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: editEquipment.name,
          quantity: editEquipment.quantity,
          description: editEquipment.description,
        }),
      });
  
      const data = await response.json();
  
      if (data.status === 'success') {
        console.log('Equipment updated:', data.equipment);
        await fetchEquipment();
        setEditEquipment({ id: '', name: '', quantity: 0, description: '' });
        setShowEditModal(false);
      } else {
        console.error('Update failed:', data.message);
      }
    } catch (error) {
      console.error('Error during update:', error);
    }
  };  

  const handleArchiveEquipmentSubmit = async (equipmentId) => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`http://localhost:8000/api/equipment/${equipmentId}/archived`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
  
      const data = await response.json();
  
      if (data.status === 'success') {
        console.log('Equipment archived successfully', data);
        await fetchArchivedEquipment();
        await fetchEquipment();
      } else {
        console.log('Failed to archive equipment', data);
      }
    } catch (error) {
      console.error('Error archiving equipment:', error);
    }
  };
  
  const handleUnarchiveEquipmentSubmit = async (equipmentId) => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`http://localhost:8000/api/equipment/${equipmentId}/unarchive`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
  
      const data = await response.json();
  
      if (data.status === 'success') {
        console.log('Equipment unarchived successfully');
        await fetchArchivedEquipment();
        await fetchEquipment();
      } else {
        console.error('Failed to unarchive equipment:', data.message || data);
      }
    } catch (error) {
      console.error('Error unarchiving equipment:', error);
    }
  };

  const handleEditEquipmentChange = (e) => {
    const { name, value } = e.target;
    setEditEquipment((prev) => ({
      ...prev,
      [name]: name === 'quantity' ? parseInt(value) : value
    }));
  };

  const handleRestockChange = (e) => {
    const { name, value } = e.target;
    setRestockData({ ...restockData, [name]: value });
  };

  const roleLabelMap = {
    admin: 'Admin',
    emp: 'Employee',
    inventory: 'Inventory Manager',
    finance: 'Finance Admin',
    pm: 'Project Manager',
    sc: 'Site Coordinator',
    client: 'Client',
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
                className="add-equipment-modal"
                onClick={() => setShowAddModal(true)}
              >
                <AddCircleOutlineIcon className="me-2 me-md-2" />
                <span className="d-none d-md-inline">Add New Equipment</span>
              </Button>
              <Button
                variant="primary"
                className="restock-equipment-modal"
                onClick={() => setShowRestockModal(true)}
              >
                <RestockIcon className="me-0 me-md-2" />
                <span className="d-none d-md-inline">Restock Equipment</span>
              </Button>
              <Button
                variant="primary"
                className="edit-equipment-modal"
                onClick={() => setShowEditModal(true)}
              >
                <EditIcon className="me-0 me-md-2" />
                <span className="d-none d-md-inline">Edit Equipment</span>
              </Button>
              <Button
                variant="primary"
                className="archive-equipment-modal"
                onClick={() => setShowArchiveEquipmentModal(true)}
              >
                <ArchiveIcon className="me-0 me-md-2" />
                <span className="d-none d-md-inline">Archive Equipment</span>
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
                      <th>Description</th>
                      <th>Quantity</th>
                      <th>Status</th>
                      <th>Archived</th>
                    </tr>
                  </thead>
                  <tbody>
                    {equipment.filter(item => !item.archived).length === 0 ? (
                      <tr>
                        <td colSpan="6" className="text-center">No active equipment found.</td>
                      </tr>
                    ) : (
                      equipment
                        .filter(item => !item.archived)
                        .map((item, index) => (
                          <tr key={item.id}>
                            <td>{index + 1}</td>
                            <td>{item.name}</td>
                            <td>{item.description}</td>
                            <td>{item.quantity}</td>
                            <td>{item.quantity <= item.min_stock_level ? 'Low' : 'In Stock'}</td>
                            <td>{item.archived ? 'Yes' : 'No'}</td>
                          </tr>
                        ))
                    )}
                  </tbody>
                </Table>

                </Card.Body>
              </Card>
            </Col>
          </Row>


          {/* Add Equipment Modal */}
          <Modal show={showAddModal} onHide={() => setShowAddModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Add New Equipment</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleAddSubmit}>
              <Form.Group className="mb-3">
                <Form.Label>Equipment Name</Form.Label>
                <Form.Control
                  type="text"
                  value={newEquipment.name}
                  onChange={(e) => setNewEquipment({ ...newEquipment, name: e.target.value })}
                />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Initial Quantity</Form.Label>
                <Form.Control
                  type="number"
                  value={newEquipment.quantity}
                  onChange={(e) => { const value = e.target.value; setNewEquipment({ ...newEquipment, quantity: value === '' ? '' : parseInt(value)});}}
                />
              </Form.Group>

              <Form.Group className="mb-3">
                <Form.Label>Description</Form.Label>
                <Form.Control
                  as="textarea"
                  rows={3}
                  value={newEquipment.description}
                  onChange={(e) => setNewEquipment({ ...newEquipment, description: e.target.value })}
                />
              </Form.Group>
                <div className="d-flex gap-2 justify-content-end">
                  <Button variant="secondary" onClick={() => setShowAddModal(false)}>
                    Cancel
                  </Button>
                  <Button variant="primary" type="submit">
                    Add Equipment
                  </Button>                                
                </div>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Restock Modal */}
          <Modal show={showRestockModal} onHide={() => setShowRestockModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Restock Equipment</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleRestockSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Select Equipment</Form.Label>
                  <Form.Select 
                    name="equipmentId" 
                    value={restockData.equipmentId} 
                    onChange={handleRestockChange} 
                    required
                  >
                    <option value="">Choose equipment to restock...</option>
                    {equipment.filter(item => !item.archived).map(item => (
                      <option key={item.id} value={item.id}>
                        {item.name} (Current: {item.quantity})
                      </option>
                    ))}
                  </Form.Select>
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Amount to Add</Form.Label>
                  <Form.Control 
                    type="number" 
                    name="amount" 
                    value={restockData.amount} 
                    onChange={handleRestockChange} 
                    min="1" 
                    required 
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Note (Optional)</Form.Label>
                  <Form.Control 
                    as="textarea" 
                    name="note" 
                    value={restockData.note} 
                    onChange={handleRestockChange} 
                    rows={2} 
                    placeholder="Optional note about this restock..."
                  />
                </Form.Group>
                <div className="d-flex gap-2 justify-content-end">
                  <Button variant="secondary" onClick={() => setShowRestockModal(false)}>
                    Cancel
                  </Button>
                  <Button variant="primary" type="submit">
                    Restock Equipment
                  </Button>
                </div>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showEditModal} onHide={() => setShowEditModal(false)} centered size="lg">
            <Modal.Header closeButton>
              <Modal.Title>Update Equipment</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleEditSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Select Equipment to Edit</Form.Label>
                  <Form.Select 
                    value={editEquipment.id || ''} 
                    onChange={(e) => {
                      const selectedEquipment = equipment.find(item => item.id == e.target.value);
                      if (selectedEquipment) {
                        setEditEquipment({
                          id: selectedEquipment.id,
                          name: selectedEquipment.name,
                          quantity: selectedEquipment.quantity,
                          description: selectedEquipment.description || ''
                        });
                      }
                    }}
                    required
                  >
                    <option value="">Choose equipment to edit...</option>
                    {equipment.filter(item => !item.archived).map(item => (
                      <option key={item.id} value={item.id}>{item.name}</option>
                    ))}
                  </Form.Select>
                </Form.Group>

                {editEquipment.id && (
                  <>
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
                        min="0"
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

                    <div className="d-flex gap-2 justify-content-end">
                      <Button variant="primary" type="submit">
                        Save Changes
                      </Button>
                      <Button variant="secondary" onClick={() => setShowEditModal(false)}>
                        Cancel
                      </Button>                      
                    </div>
                  </>
                )}
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showArchiveEquipmentModal} onHide={() => setShowArchiveEquipmentModal(false)} centered size="xl">
            <Modal.Header closeButton>
              <Modal.Title>Manage Equipment</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <h5 className="mt-4 mb-3">Active Equipments</h5>
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
                  {equipment.filter(item => !item.archived).map((item, index) => (
                    <tr key={item.id}>
                      <td>{index + 1}</td>
                      <td>{item.name}</td>
                      <td>{item.quantity}</td>
                      <td>{item.description}</td>
                      <td>
                        <Button
                          variant="danger"
                          size="sm"
                          onClick={() => handleArchiveEquipmentSubmit(item.id)}
                        >
                          Archive
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </Table>

              <h5 className="mt-4 mb-3">Archived Equipment</h5>
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
                  {archivedEquipment.length === 0 ? (
                    <tr>
                      <td colSpan="5" className="text-center">No archived equipment found.</td>
                    </tr>
                  ) : (
                    archivedEquipment.map((item, index) => (
                      <tr key={item.id}>
                        <td>{index + 1}</td>
                        <td>{item.name}</td>
                        <td>{item.quantity}</td>
                        <td>{item.description}</td>
                        <td>
                          <Button
                            variant="success"
                            size="sm"
                            onClick={() => handleUnarchiveEquipmentSubmit(item.id)}
                          >
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
        </Container>
      </main>
    </div>
  );
};

export default InventoryManagement;