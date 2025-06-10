import React, { useState, useEffect } from "react";
import { Container, Row, Col, Card, Table, Form, ProgressBar } from "react-bootstrap";
import axios from "axios"; 

const BigContainer = () => {
  const [data, setData] = useState([]); 

  useEffect(() => {
    
    const fetchData = async () => {
      try {
        const response = await axios.get("http://localhost:5000/api/projects"); 
        setData(response.data);
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    };

    fetchData();
  }, []);

  return (
    <Container fluid className="big-container">
      <Row>
        <Col>
          <Card className="dashboard-card mb-4">
            <Card.Body>
              <div className="d-flex justify-content-between align-items-center mb-4">
                <Card.Title className="mb-0">Project Progress Tracker</Card.Title>
                <Form.Select size="sm" style={{ width: "auto" }}>
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
                    {data.map((row, idx) => (
                      <tr key={idx}>
                        <td>{row.date}</td>
                        <td>{row.task}</td>
                        <td>{row.coordinator}</td>
                        <td>{row.equipments}</td>
                        <td>{row.workers}</td>
                        <td>
                          <button className="btn btn-sm btn-primary">Details</button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default BigContainer;