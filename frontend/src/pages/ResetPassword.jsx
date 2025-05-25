import React, { useState } from 'react';
import { Form, Button, Container, Row, Col, Card } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft } from 'react-bootstrap-icons';
import axios from 'axios';

const ForgotPassword = () => {
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      await axios.post('http://localhost:8000/api/forgot-password', {
        email,
      });
      setMessage('Password reset link has been sent to your email.');
    } catch {
      setMessage('Something went wrong. Please try again.');
    }
  };

  return (
    <Container className="py-5">
      <Row className="justify-content-center">
        <Col md={8} lg={6}>
          <Card className="reset-password-card">
            <Card.Body className="p-4 position-relative">
              <Button
                variant="link"
                className="back-button-circle position-absolute"
                style={{ top: '15px', left: '15px' }}
                onClick={() => navigate('/login')}
              >
                <ArrowLeft size={20} />
              </Button>

              <h2 className="text-center mb-4">Forgot Password</h2>

              {message && <p className="text-success text-center">{message}</p>}

              <Form onSubmit={handleSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Email address</Form.Label>
                  <Form.Control
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </Form.Group>
                <Button variant="primary" type="submit" className="w-100">
                  Send Reset Link
                </Button>
              </Form>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default ForgotPassword;
