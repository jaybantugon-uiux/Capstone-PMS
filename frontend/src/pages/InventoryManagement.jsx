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
  const [newEquipment, setNewEquipment] = useState({ name: '', quantity: 0, description: '' });
  const [editEquipment, setEditEquipment] = useState({});
  const [restockData, setRestockData] = useState({ equipmentId: '', amount: 0, note: '' });
  const [showAddModal, setShowAddModal] = useState(false);
  const [showRestockModal, setShowRestockModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showArchiveModal, setShowArchiveModal] = useState(false);

  useEffect(() => {
    // Initialize with empty data - ready for your custom functions
  }, []);

  const handleAddSubmit = (e) => {
    e.preventDefault();
    
    // Add your custom function here
    console.log('Add equipment:', newEquipment);
  };

  const handleRestockSubmit = (e) => {
    e.preventDefault();
    
    // Add your custom restock function here
    console.log('Restock equipment:', restockData);
  };

  const handleEditSubmit = (e) => {
    e.preventDefault();
    
    // Add your custom edit function here
    console.log('Edit equipment:', editEquipment);
  };

  const handleArchiveSubmit = (equipmentId) => {
    // Add your custom archive function here
    console.log('Archive equipment ID:', equipmentId);
  };

  const handleEquipmentChange = (e) => {
    const { name, value } = e.target;
    setNewEquipment({ ...newEquipment, [name]: value });
  };

  const handleRestockChange = (e) => {
    const { name, value } = e.target;
    setRestockData({ ...restockData, [name]: value });
  };

  const handleEditEquipmentChange = (e) => {
    const { name, value } = e.target;
    setEditEquipment({ ...editEquipment, [name]: value });
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

          {/* Actions */}
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
                onClick={() => setShowArchiveModal(true)}
              >
                <ArchiveIcon className="me-0 me-md-2" />
                <span className="d-none d-md-inline">Archive Equipment</span>
              </Button>
            </Col>
          </Row>

          {/* Equipment Table */}
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
                    name="name" 
                    value={newEquipment.name} 
                    onChange={handleEquipmentChange} 
                    required 
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Initial Quantity</Form.Label>
                  <Form.Control 
                    type="number" 
                    name="quantity" 
                    value={newEquipment.quantity} 
                    onChange={handleEquipmentChange} 
                    required 
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control 
                    as="textarea" 
                    name="description" 
                    value={newEquipment.description} 
                    onChange={handleEquipmentChange} 
                    rows={3}
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

          {/* Edit Modal */}
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
                    <div className="d-flex gap-2 justify-content-end">
                      <Button variant="secondary" onClick={() => setShowEditModal(false)}>
                        Cancel
                      </Button>
                      <Button variant="primary" type="submit">
                        Save Changes
                      </Button>
                    </div>
                  </>
                )}
              </Form>
            </Modal.Body>
          </Modal>

          {/* Archive Modal */}
          <Modal show={showArchiveModal} onHide={() => setShowArchiveModal(false)} centered size="lg">
            <Modal.Header closeButton>
              <Modal.Title>Archive Equipment</Modal.Title>
            </Modal.Header>
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
                  {equipment.filter(item => !item.archived).map((item, index) => (
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