import React, { useState } from 'react';
import { Form, Button, Container, Row, Col, Card } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, Google } from 'react-bootstrap-icons';

const Login = () => {
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const [showPassword, setShowPassword] = useState(false);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const toggleShowPassword = () => {
    setShowPassword(prev => !prev);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Add your login logic here
    console.log(formData);
  };

  return (
    <Container className="py-5">
      <Row className="justify-content-center">
        <Col md={8} lg={6}>
          <Card className="login-card">
            <Card.Body className="p-4 position-relative">
              <Button 
                variant="link" 
                className="back-button-circle"
                onClick={() => navigate('/')}
              >
                <ArrowLeft size={20} />
              </Button>
              <h2 className="text-center mb-4">Login</h2>
              <Form onSubmit={handleSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Email address</Form.Label>
                  <Form.Control
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                  />
                </Form.Group>

                <Form.Group className="mb-2">
                  <Form.Label>Password</Form.Label>
                  <Form.Control
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    required
                  />
                </Form.Group>

                <div className="d-flex justify-content-between align-items-center mb-3">
                  <Form.Check
                    type="checkbox"
                    label="Show Password"
                    onChange={toggleShowPassword}
                    checked={showPassword}
                  />
                  <Link to="/resetPassword">Forgot Password?</Link>
                </div>

                <Button variant="primary" type="submit" className="w-100 mb-3">
                  Login
                </Button>

                <div className="text-center mb-3">
                  Create new account? <Link to="/signup">Sign up here</Link>
                </div>

                <Button 
                  variant="outline-secondary" 
                  className="google-direct w-100 d-flex align-items-center justify-content-center gap-2 mb-3"
                >
                  <Google size={16} />
                  Continue with Google
                </Button>
              </Form>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default Login;
