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

        </Container>
      </main>
    </div>
  );
};

export default DashboardMain;