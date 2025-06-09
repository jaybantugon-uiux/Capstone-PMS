import React, { useState } from 'react';
import { Form, Button, Container, Row, Col, Card } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, Google } from 'react-bootstrap-icons';
import axios from 'axios'; // Add this import

const Login = () => {
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState(''); // Add error state

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    setError(''); // Clear error when user types
  };

  const toggleShowPassword = () => {
    setShowPassword(prev => !prev);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
        const response = await axios.post('http://localhost:8000/api/auth/login', {
            email: formData.email,
            password: formData.password,
        });

        if (response.data.status === 'success') {
            const { token, user } = response.data;
            
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));

            switch (user.role) {
                case 'admin':
                    navigate('/admin-dashboard');
                    break;
                case 'emp':
                    navigate('/employee-dashboard');
                    break;
                case 'finance':
                    navigate('/finance-dashboard');
                    break;
                case 'pm':
                    navigate('/pm-dashboard');
                    break;
                case 'sc':
                    navigate('/sc-dashboard');
                    break;
                case 'client':
                default:
                    navigate('/client-dashboard');
                    break;
            }
        }
    } catch (error) {
        setError(error.response?.data?.message || 'An error occurred during login');
    }
  };

  return (
    <Container className="py-5">
      <Row className="justify-content-center">
        <Col md={8} lg={6}>
          <Card className="login-card">
            <Card.Body className="p-4 position-relative">
              <div className="form-container">
                <button
                  className="back-button-circle"
                  onClick={() => navigate('/')}
                >
                  <ArrowLeft size={20}/>
                </button>
                <h2 className="text-center mb-4">Log In</h2>
              </div>
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

                {error && (
                  <div className="alert alert-danger mt-3">
                    {error}
                  </div>
                )}

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
