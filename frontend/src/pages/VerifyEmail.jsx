import React from 'react';
import { Container, Card, Button, Row, Col } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';
import { Envelope, ArrowLeft } from 'react-bootstrap-icons';

const VerifyEmail = () => {
  const navigate = useNavigate();
  const email = localStorage.getItem('pendingVerificationEmail');

  return (
    <Container className="py-5">
      <Row className="justify-content-center">
        <Col md={8} lg={6}>
          <Card className="verify-card">
            <Card.Body className="p-4 position-relative">
              <Button 
                variant="link" 
                className="back-button-circle"
                onClick={() => navigate('/login')}
              >
                <ArrowLeft size={20} />
              </Button>
              
              <div className="text-center">
                <div className="mb-4">
                  <Envelope size={50} className="text-primary" />
                </div>
                
                <Card.Title as="h2" className="mb-4">Verify your email address</Card.Title>
                
                <Card.Text className="mb-2">
                  We've sent a verification link to:
                </Card.Text>
                <Card.Text className="mb-4">
                  <strong className="text-primary">{email || 'your email address'}</strong>
                </Card.Text>
                
                <Card.Text className="text-muted mb-4">
                  Please check your email and click on the verification link to continue.
                  If you don't see the email, check your spam folder.
                </Card.Text>

                <div className="d-grid gap-2">
                  <Button 
                    variant="primary" 
                    className="mb-3"
                    onClick={() => window.location.href = "https://mail.google.com"}
                  >
                    Open Gmail
                  </Button>
                  
                  <Button 
                    variant="outline-secondary"
                    onClick={() => navigate('/login')}
                  >
                    Back to Login
                  </Button>
                </div>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default VerifyEmail;