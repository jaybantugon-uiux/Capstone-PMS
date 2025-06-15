import React, { useState, useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import { Container, Row, Col, Card, Button, Table, Modal, Form } from 'react-bootstrap';
import { PersonCircle } from 'react-bootstrap-icons';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import EditIcon from '@mui/icons-material/Edit';
import ArchiveIcon from '@mui/icons-material/Archive';
import RestoreIcon from '@mui/icons-material/Restore';
import FileDownloadIcon from '@mui/icons-material/FileDownload';
import '../css/Dashboard.css';

const FileManagement = () => {
  const [userRole, setUserRole] = useState('');
  const [files, setFiles] = useState([]);
  const [newFile, setNewFile] = useState({ name: '', description: '', file: null });
  const [editFile, setEditFile] = useState({});
  const [showUploadModal, setShowUploadModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showArchiveModal, setShowArchiveModal] = useState(false);

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const user = JSON.parse(userData);
      setUserRole(user.role);
    }

    fetchFiles();
  }, []);

  const fetchFiles = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/files', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      setFiles(data.files);
    } catch (error) {
      console.error('Error fetching files:', error);
    }
  };

  const handleUploadSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('name', newFile.name);
    formData.append('description', newFile.description);
    formData.append('file', newFile.file);

    try {
      const response = await fetch('http://localhost:8000/api/files/upload', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: formData,
      });
      const data = await response.json();
      if (data.status === 'success') {
        setFiles([...files, data.file]);
        setNewFile({ name: '', description: '', file: null });
        setShowUploadModal(false);
      }
    } catch (error) {
      console.error('Error uploading file:', error);
    }
  };

  const handleEditSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`http://localhost:8000/api/files/${editFile.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(editFile),
      });
      const data = await response.json();
      if (data.status === 'success') {
        setFiles(files.map((file) => (file.id === editFile.id ? data.file : file)));
        setShowEditModal(false);
      }
    } catch (error) {
      console.error('Error editing file:', error);
    }
  };

  const handleArchiveFile = async (fileId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/files/${fileId}/archive`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      if (data.status === 'success') {
        setFiles(files.filter((file) => file.id !== fileId));
      }
    } catch (error) {
      console.error('Error archiving file:', error);
    }
  };

  const handleRestoreFile = async (fileId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/files/${fileId}/restore`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      if (data.status === 'success') {
        setFiles([...files, data.file]);
      }
    } catch (error) {
      console.error('Error restoring file:', error);
    }
  };

  const handleExportFile = async (fileId) => {
    try {
      const response = await fetch(`http://localhost:8000/api/files/${fileId}/export`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'file_export.zip';
      link.click();
    } catch (error) {
      console.error('Error exporting file:', error);
    }
  };

  const handleFileChange = (e) => {
    const { name, value, files } = e.target;
    if (name === 'file') {
      setNewFile({ ...newFile, file: files[0] });
    } else {
      setNewFile({ ...newFile, [name]: value });
    }
  };

  const handleEditFileChange = (e) => {
    const { name, value } = e.target;
    setEditFile({ ...editFile, [name]: value });
  };

  const roleLabelMap = {
    employee: 'Employee',
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
            <Col className="d-flex justify-content-end gap-2">
              <Button
                variant="primary"
                className="upload-file-modal"
                onClick={() => setShowUploadModal(true)}
              >
                <AddCircleOutlineIcon className="me-2" />
                <span className="d-none d-md-inline">Upload File</span>
              </Button>
              <Button
                variant="primary"
                className="edit-file-modal"
                onClick={() => setShowEditModal(true)}
              >
                <EditIcon className="me-2" />
                <span className="d-none d-md-inline">Modify File</span>
              </Button>
              <Button
                variant="danger"
                className="archive-file-modal"
                onClick={() => setShowArchiveModal(true)}
              >
                <ArchiveIcon className="me-2" />
                <span className="d-none d-md-inline">Archive File</span>
              </Button>
            </Col>
          </Row>

          <Row>
            <Col>
              <Card className="mb-4">
                <Card.Header>
                  <h5>File List</h5>
                </Card.Header>
                <Card.Body>
                  <Table responsive bordered hover>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      {files.map((file, index) => (
                        <tr key={file.id}>
                          <td>{index + 1}</td>
                          <td>{file.name}</td>
                          <td>{file.description}</td>
                          <td>
                            <Button
                              variant="danger"
                              size="sm"
                              onClick={() => handleArchiveFile(file.id)}
                            >
                              Archive
                            </Button>
                            <Button
                              variant="success"
                              size="sm"
                              onClick={() => handleRestoreFile(file.id)}
                            >
                              Restore
                            </Button>
                            <Button
                              variant="info"
                              size="sm"
                              onClick={() => handleExportFile(file.id)}
                            >
                              Export
                            </Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          <Modal show={showUploadModal} onHide={() => setShowUploadModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Upload File</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleUploadSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>File Name</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    value={newFile.name}
                    onChange={handleFileChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    type="text"
                    name="description"
                    value={newFile.description}
                    onChange={handleFileChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>File</Form.Label>
                  <Form.Control
                    type="file"
                    name="file"
                    onChange={handleFileChange}
                    required
                  />
                </Form.Group>
                <Button variant="primary" type="submit">
                  Upload
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showEditModal} onHide={() => setShowEditModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Modify File</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form onSubmit={handleEditSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>File Name</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    value={editFile.name || ''}
                    onChange={handleEditFileChange}
                    required
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Description</Form.Label>
                  <Form.Control
                    type="text"
                    name="description"
                    value={editFile.description || ''}
                    onChange={handleEditFileChange}
                    required
                  />
                </Form.Group>
                <Button variant="primary" type="submit">
                  Save Changes
                </Button>
              </Form>
            </Modal.Body>
          </Modal>

          <Modal show={showArchiveModal} onHide={() => setShowArchiveModal(false)} centered>
            <Modal.Header closeButton>
              <Modal.Title>Archive File</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <h5>Select a file to archive:</h5>
              <Table responsive bordered hover>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {files.map((file, index) => (
                    <tr key={file.id}>
                      <td>{index + 1}</td>
                      <td>{file.name}</td>
                      <td>{file.description}</td>
                      <td>
                        <Button
                          variant="danger"
                          size="sm"
                          onClick={() => handleArchiveFile(file.id)}
                        >
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

export default FileManagement;