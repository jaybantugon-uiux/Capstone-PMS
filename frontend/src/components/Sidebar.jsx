import React, { useState } from 'react';
import { Navbar, Offcanvas, Nav, Button } from 'react-bootstrap';
import { List, House, Folder, FileText, CreditCard, Truck, Users, Gear } from 'react-bootstrap-icons';
import './Sidebar.css';

const Sidebar = () => {
  const [show, setShow] = useState(false);

  const handleToggle = () => setShow(!show);
  const handleClose = () => setShow(false);

  const navItems = (
    <Nav className="flex-column gap-2">
      <Nav.Link href="/dashboard"><House className="me-2" /> Dashboard</Nav.Link>
      <Nav.Link href="/projects"><Folder className="me-2" /> Projects</Nav.Link>
      <Nav.Link href="/files"><FileText className="me-2" /> Files</Nav.Link>
      <Nav.Link href="/expenses"><CreditCard className="me-2" /> Expenses</Nav.Link>
      <Nav.Link href="/equipments"><Truck className="me-2" /> Equipments</Nav.Link>
      <Nav.Link href="/users"><Users className="me-2" /> Users</Nav.Link>
      <Nav.Link href="/settings"><Gear className="me-2" /> Settings</Nav.Link>
    </Nav>
  );

  return (
    <>
      {/* Top Navbar for mobile toggle */}
      <Navbar bg="dark" variant="dark" className="d-md-none px-3">
        <Button variant="outline-light" onClick={handleToggle}>
          <List size={20} />
        </Button>
        <Navbar.Brand className="ms-3">Project R</Navbar.Brand>
      </Navbar>

      {/* Offcanvas Sidebar for small screens */}
      <Offcanvas show={show} onHide={handleClose} className="bg-dark text-white" responsive="md">
        <Offcanvas.Header closeButton closeVariant="white">
          <Offcanvas.Title>Project R</Offcanvas.Title>
        </Offcanvas.Header>
        <Offcanvas.Body>
          <div className="mb-4 border-bottom pb-3">
            <strong>Jay Louis Bantugan</strong>
            <br />
            <small>Admin</small>
          </div>
          {navItems}
        </Offcanvas.Body>
      </Offcanvas>

      {/* Static Sidebar for desktop */}
      <div className="d-none d-md-flex flex-column bg-dark text-white vh-100 p-3" style={{ width: '240px' }}>
        <div className="mb-4 text-center">
          <img src="/logo192.png" alt="Logo" width="50" height="50" className="mb-2" />
          <h5>Project R</h5>
        </div>
        <div className="mb-4 border-bottom pb-3">
          <strong>Jay Louis Bantugan</strong>
          <br />
          <small>Admin</small>
        </div>
        {navItems}
      </div>
    </>
  );
};

export default Sidebar;
