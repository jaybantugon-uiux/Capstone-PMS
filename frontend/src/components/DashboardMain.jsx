import React, { useEffect, useState } from 'react';
import Sidebar from './Sidebar.jsx';
import { Container, Row, Col, Card, ProgressBar, Table, Form } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';

const DashboardMain = () => {

  const [userRole, setUserRole] = useState('');

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }
  }, []);

  const roleLabelMap = {
    admin: 'Admin',
    emp: 'Employee',
    finance: 'Finance Admin',
    pm: 'Project Manager',
    sc: 'Site Coordinator',
    client: 'Client'
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
                <Form.Select size="sm" className="border-0 bg-transparent" style={{ width: 'auto' }} disabled>
                  <option>{roleLabelMap[userRole] || 'User'}</option> 
                </Form.Select>
              </div>
            </Col>
          </Row>

          <Row className="g-3 mb-4">
            <Col xs={12} md={4}>
              <Card className="dashboard-card h-100">
                <Card.Body>
                  <Card.Title className="fs-6 text-muted">Current Projects</Card.Title>
                  <Card.Text className="fs-2 fw-bold">3</Card.Text>
                </Card.Body>
              </Card>
            </Col>
            
          </Row>

          <Row className="g-3 mb-4">
            <Col xs={12} md={4}>
              <Card className="dashboard-card h-100">
                <Card.Body>
                  <Card.Title className="fs-6 text-muted">Available Equipments</Card.Title>
                  <Card.Text className="fs-2 fw-bold">50/65</Card.Text>
                  <ProgressBar now={76} variant="success" />
                </Card.Body>
              </Card>
            </Col>
            <Col xs={12} md={4}>
              <Card className="dashboard-card h-100">
                <Card.Body>
                  <Card.Title className="fs-6 text-muted">Available Site Coordinators</Card.Title>
                  <Card.Text className="fs-2 fw-bold">1/3</Card.Text>
                  <ProgressBar now={33} variant="warning" />
                </Card.Body>
              </Card>
            </Col>
            <Col xs={12} md={4}>
              <Card className="dashboard-card h-100">
                <Card.Body>
                  <Card.Title className="fs-6 text-muted">Pending Approval</Card.Title>
                  <Card.Text className="fs-2 fw-bold">3/5</Card.Text>
                  <ProgressBar now={60} variant="info" />
                </Card.Body>
              </Card>
            </Col>
          </Row>

          <Card className="dashboard-card mb-4">
            <Card.Body>
              <div className="d-flex justify-content-between align-items-center mb-4">
                <Card.Title className="mb-0">Project Progress Tracker</Card.Title>
                <Form.Select size="sm" style={{ width: 'auto' }}>
                  <option>Google Office</option>
                </Form.Select>
              </div>
              
              <div className="progress-timeline mb-4">
                <ProgressBar>
                  <ProgressBar variant="success" now={40} key={1} />
                  <ProgressBar variant="secondary" now={60} key={2} />
                </ProgressBar>
              </div>

              <div className="table-responsive">
                <Table hover className="dashboard-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Task Accomplished</th>
                      <th>Site Coordinator</th>
                      <th>Equipments Used</th>
                      <th>Workers</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {/* Table rows will be populated from database */}
                  </tbody>
                </Table>
              </div>
            </Card.Body>
          </Card>
        </Container>
      </main>
    </div>
  );
};

export default DashboardMain;