import React, { useState } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Form, Modal } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import LockResetIcon from '@mui/icons-material/LockReset';
import EmailIcon from '@mui/icons-material/Email';
import '../css/Dashboard.css';

const UserManagement = () => {
  const [userRole] = useState('admin');
  const [users, setUsers] = useState([]);
  const [showRegisterModal, setShowRegisterModal] = useState(false);
  const [showUpdateModal, setShowUpdateModal] = useState(false);
  const [showChangePasswordModal, setShowChangePasswordModal] = useState(false);
  const [showDeactivateModal, setShowDeactivateModal] = useState(false);
  const [showResetPasswordModal, setShowResetPasswordModal] = useState(false);
  const [showSendVerificationModal, setShowSendVerificationModal] = useState(false);

  const handleRegister = (e) => { e.preventDefault(); setShowRegisterModal(false); };
  const handleUpdate = (e) => { e.preventDefault(); setShowUpdateModal(false); };
  const handleChangePassword = (e) => { e.preventDefault(); setShowChangePasswordModal(false); };
  const handleDeactivate = (e) => { e.preventDefault(); setShowDeactivateModal(false); };
  const handleResetPassword = (e) => { e.preventDefault(); setShowResetPasswordModal(false); };
  const handleSendVerification = (e) => { e.preventDefault(); setShowSendVerificationModal(false); };

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
            <Col className="d-flex justify-content-end gap-2 flex-wrap">
              <Button variant="primary" onClick={() => setShowRegisterModal(true)}>
                <AddCircleOutlineIcon className="me-2" />
                <span className="d-none d-md-inline">Register</span>
              </Button>
              <Button variant="primary" onClick={() => setShowUpdateModal(true)}>
                <EditIcon className="me-2" />
                <span className="d-none d-md-inline">Update Account Details</span>
              </Button>
              <Button variant="primary" onClick={() => setShowChangePasswordModal(true)}>
                <LockResetIcon className="me-2" />
                <span className="d-none d-md-inline">Change Password</span>
              </Button>
              <Button variant="danger" onClick={() => setShowDeactivateModal(true)}>
                <DeleteIcon className="me-2" />
                <span className="d-none d-md-inline">Deactivate Account</span>
              </Button>
              <Button variant="secondary" onClick={() => setShowResetPasswordModal(true)}>
                <LockResetIcon className="me-2" />
                <span className="d-none d-md-inline">Reset Password</span>
              </Button>
              <Button variant="info" onClick={() => setShowSendVerificationModal(true)}>
                <EmailIcon className="me-2" />
                <span className="d-none d-md-inline">Send Verification Email</span>
              </Button>
            </Col>
          </Row>

          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header>
                  <h5>User List</h5>
                </Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {users.length === 0 ? (
                        <tr>
                          <td colSpan={6} className="text-center">No users found.</td>
                        </tr>
                      ) : (
                        users.map((user, idx) => (
                          <tr key={user.id}>
                            <td>{idx + 1}</td>
                            <td>{user.name}</td>
                            <td>{user.email}</td>
                            <td>{roleLabelMap[user.role] || user.role}</td>
                            <td>{user.active ? 'Active' : 'Deactivated'}</td>
                            <td>
                              <Button size="sm" variant="outline-primary" onClick={() => setShowUpdateModal(true)}>
                                Edit
                              </Button>
                              <Button size="sm" variant="outline-danger" className="ms-2" onClick={() => setShowDeactivateModal(true)}>
                                Deactivate
                              </Button>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          {/* Register Modal */}
          <Modal show={showRegisterModal} onHide={() => setShowRegisterModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Register User</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleRegister}>
                <Form.Group className="mb-3">
                  <Form.Label>Name</Form.Label>
                  <Form.Control type="text" required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Email</Form.Label>
                  <Form.Control type="email" required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Password</Form.Label>
                  <Form.Control type="password" required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Role</Form.Label>
                  <Form.Select required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="emp">Employee</option>
                    <option value="finance">Finance Admin</option>
                    <option value="pm">Project Manager</option>
                    <option value="sc">Site Coordinator</option>
                    <option value="client">Client</option>
                  </Form.Select>
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Register</Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Update Account Modal */}
          <Modal show={showUpdateModal} onHide={() => setShowUpdateModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Update Account Details</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleUpdate}>
                <Form.Group className="mb-3">
                  <Form.Label>Name</Form.Label>
                  <Form.Control type="text" required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Email</Form.Label>
                  <Form.Control type="email" required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Update</Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Change Password Modal */}
          <Modal show={showChangePasswordModal} onHide={() => setShowChangePasswordModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Change Password</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleChangePassword}>
                <Form.Group className="mb-3">
                  <Form.Label>New Password</Form.Label>
                  <Form.Control type="password" required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Confirm New Password</Form.Label>
                  <Form.Control type="password" required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Change Password</Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Deactivate Account Modal */}
          <Modal show={showDeactivateModal} onHide={() => setShowDeactivateModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Deactivate Account</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <p>Are you sure you want to deactivate this account?</p>
              <Button variant="danger" className="w-100" onClick={handleDeactivate}>Deactivate</Button>
            </Modal.Body>
          </Modal>

          {/* Reset Password Modal */}
          <Modal show={showResetPasswordModal} onHide={() => setShowResetPasswordModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Reset Password</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleResetPassword}>
                <Form.Group className="mb-3">
                  <Form.Label>Email</Form.Label>
                  <Form.Control type="email" required />
                </Form.Group>
                <Button type="submit" variant="secondary" className="w-100">Send Reset Link</Button>
              </Form>
            </Modal.Body>
          </Modal>

          {/* Send Verification Email Modal */}
          <Modal show={showSendVerificationModal} onHide={() => setShowSendVerificationModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Send Verification Email</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleSendVerification}>
                <Form.Group className="mb-3">
                  <Form.Label>Email</Form.Label>
                  <Form.Control type="email" required />
                </Form.Group>
                <Button type="submit" variant="info" className="w-100">Send Verification Email</Button>
              </Form>
            </Modal.Body>
          </Modal>
        </Container>
      </main>
    </div>
  );
};

export default UserManagement;