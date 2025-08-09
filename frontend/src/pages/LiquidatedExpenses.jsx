import React, { useState } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Form, Modal } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import PrintIcon from '@mui/icons-material/Print';
import EditIcon from '@mui/icons-material/Edit';
import FlagIcon from '@mui/icons-material/Flag';
import UploadFileIcon from '@mui/icons-material/UploadFile';
import DescriptionIcon from '@mui/icons-material/Description';
import AssignmentReturnIcon from '@mui/icons-material/AssignmentReturn';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
import '../css/Dashboard.css';

const LiquidatedExpenses = () => {
  const [userRole] = useState('finance');
  const [expenses, setExpenses] = useState([]);
  const [showSubmitModal, setShowSubmitModal] = useState(false);
  const [showReportModal, setShowReportModal] = useState(false);
  const [showUploadModal, setShowUploadModal] = useState(false);
  const [showViewModal, setShowViewModal] = useState(false);
  const [showUpdateModal, setShowUpdateModal] = useState(false);
  const [showFlagModal, setShowFlagModal] = useState(false);
  const [showPrintModal, setShowPrintModal] = useState(false);
  const [showRevisionModal, setShowRevisionModal] = useState(false);
  const [showClarificationModal, setShowClarificationModal] = useState(false);

  // Dummy handlers for modals
  const handleSubmitExpense = (e) => { e.preventDefault(); setShowSubmitModal(false); };
  const handleWriteReport = (e) => { e.preventDefault(); setShowReportModal(false); };
  const handleUploadReceipt = (e) => { e.preventDefault(); setShowUploadModal(false); };
  const handleUpdateExpense = (e) => { e.preventDefault(); setShowUpdateModal(false); };
  const handleFlagExpense = (e) => { e.preventDefault(); setShowFlagModal(false); };
  const handleRequestRevision = (e) => { e.preventDefault(); setShowRevisionModal(false); };
  const handleRequestClarification = (e) => { e.preventDefault(); setShowClarificationModal(false); };

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
              <Button variant="primary" onClick={() => setShowSubmitModal(true)}>
                <AddCircleOutlineIcon className="me-2" />
                <span className="d-none d-md-inline">Submit Daily Expenditures</span>
              </Button>
              <Button variant="primary" onClick={() => setShowReportModal(true)}>
                <DescriptionIcon className="me-2" />
                <span className="d-none d-md-inline">Write Financial Report</span>
              </Button>
              <Button variant="primary" onClick={() => setShowUploadModal(true)}>
                <UploadFileIcon className="me-2" />
                <span className="d-none d-md-inline">Upload Receipts</span>
              </Button>
              <Button variant="primary" onClick={() => setShowViewModal(true)}>
                <AssignmentReturnIcon className="me-2" />
                <span className="d-none d-md-inline">View Liquidated Forms</span>
              </Button>
              <Button variant="primary" onClick={() => setShowUpdateModal(true)}>
                <EditIcon className="me-2" />
                <span className="d-none d-md-inline">Update Liquidated Forms</span>
              </Button>
              <Button variant="warning" onClick={() => setShowFlagModal(true)}>
                <FlagIcon className="me-2" />
                <span className="d-none d-md-inline">Flag Suspicious Activities</span>
              </Button>
              <Button variant="secondary" onClick={() => setShowPrintModal(true)}>
                <PrintIcon className="me-2" />
                <span className="d-none d-md-inline">Print Liquidated Forms</span>
              </Button>
            </Col>
          </Row>

          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header>
                  <h5>Liquidated Expenses List</h5>
                </Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {expenses.length === 0 ? (
                        <tr>
                          <td colSpan={6} className="text-center">No records found.</td>
                        </tr>
                      ) : (
                        expenses.map((exp, idx) => (
                          <tr key={exp.id}>
                            <td>{idx + 1}</td>
                            <td>{exp.date}</td>
                            <td>{exp.description}</td>
                            <td>{exp.amount}</td>
                            <td>{exp.status}</td>
                            <td>
                              <Button size="sm" variant="outline-primary" onClick={() => setShowViewModal(true)}>
                                View
                              </Button>
                              <Button size="sm" variant="outline-warning" className="ms-2" onClick={() => setShowFlagModal(true)}>
                                Flag
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

          {/* Modals */}
          <Modal show={showSubmitModal} onHide={() => setShowSubmitModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Submit Daily Expenditures</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleSubmitExpense}>
                <Form.Group className="mb-3">
                  <Form.Label>Date</Form.Label>
                  <Form.Control type="date" required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control as="textarea" rows={2} required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Amount</Form.Label>
                  <Form.Control type="number" min="0" step="0.01" required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Submit</Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showReportModal} onHide={() => setShowReportModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Write Financial Report</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleWriteReport}>
                <Form.Group className="mb-3">
                  <Form.Label>Report</Form.Label>
                  <Form.Control as="textarea" rows={5} required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Submit Report</Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showUploadModal} onHide={() => setShowUploadModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Upload Receipts</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleUploadReceipt}>
                <Form.Group className="mb-3">
                  <Form.Label>Receipt File</Form.Label>
                  <Form.Control type="file" required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Upload</Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showViewModal} onHide={() => setShowViewModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>View Liquidated Forms</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Button variant="outline-secondary" className="mb-2 w-100" onClick={() => setShowRevisionModal(true)}>
                <AssignmentReturnIcon className="me-2" />
                Request for Revision
              </Button>
              <Button variant="outline-info" className="mb-2 w-100" onClick={() => setShowClarificationModal(true)}>
                <HelpOutlineIcon className="me-2" />
                Request Clarification
              </Button>
              {/* Details of the selected form would go here */}
              <div className="mt-3">Form details...</div>
            </Modal.Body>
          </Modal>

          <Modal show={showUpdateModal} onHide={() => setShowUpdateModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Update Liquidated Forms</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleUpdateExpense}>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control as="textarea" rows={2} required />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Amount</Form.Label>
                  <Form.Control type="number" min="0" step="0.01" required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Update</Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showFlagModal} onHide={() => setShowFlagModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Flag Suspicious Activities</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleFlagExpense}>
                <Form.Group className="mb-3">
                  <Form.Label>Reason for Flagging</Form.Label>
                  <Form.Control as="textarea" rows={3} required />
                </Form.Group>
                <Button type="submit" variant="warning" className="w-100">Flag</Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showPrintModal} onHide={() => setShowPrintModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Print Liquidated Forms</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <p>Printing functionality goes here.</p>
              <Button variant="secondary" className="w-100" onClick={() => setShowPrintModal(false)}>
                Close
              </Button>
            </Modal.Body>
          </Modal>

          <Modal show={showRevisionModal} onHide={() => setShowRevisionModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Request for Revision</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleRequestRevision}>
                <Form.Group className="mb-3">
                  <Form.Label>Revision Details</Form.Label>
                  <Form.Control as="textarea" rows={3} required />
                </Form.Group>
                <Button type="submit" variant="primary" className="w-100">Request Revision</Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showClarificationModal} onHide={() => setShowClarificationModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Request Clarification</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleRequestClarification}>
                <Form.Group className="mb-3">
                  <Form.Label>Clarification Details</Form.Label>
                  <Form.Control as="textarea" rows={3} required />
                </Form.Group>
                <Button type="submit" variant="info" className="w-100">Request Clarification</Button>
              </Form>
            </Modal.Body>
          </Modal>
        </Container>
      </main>
    </div>
  );
};

export default LiquidatedExpenses;