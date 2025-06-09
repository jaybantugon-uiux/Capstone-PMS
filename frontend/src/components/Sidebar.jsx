import React, { useState } from "react";
import { Navbar, Nav, Offcanvas, Button } from "react-bootstrap";
import DashboardIcon from "@mui/icons-material/Dashboard";
import FolderIcon from "@mui/icons-material/Folder";
import DescriptionIcon from "@mui/icons-material/Description";
import AccountBalanceWalletIcon from "@mui/icons-material/AccountBalanceWallet";
import BuildIcon from "@mui/icons-material/Build";
import PeopleIcon from "@mui/icons-material/People";
import SettingsIcon from "@mui/icons-material/Settings";
import Logo from "../assets/druLogo.png";
import "../css/Dashboard.css";

const navLinks = [
  { icon: <DashboardIcon fontSize="medium" />, label: "Dashboard" },
  { icon: <FolderIcon fontSize="medium" />, label: "Projects" },
  { icon: <DescriptionIcon fontSize="medium" />, label: "Files" },
  { icon: <AccountBalanceWalletIcon fontSize="medium" />, label: "Expenses" },
  { icon: <BuildIcon fontSize="medium" />, label: "Equipments" },
  { icon: <PeopleIcon fontSize="medium" />, label: "Users" },
  { icon: <SettingsIcon fontSize="medium" />, label: "Settings" },
];

const Sidebar = () => {
  const [show, setShow] = useState(false);

  const handleClose = () => setShow(false);
  const handleShow = () => setShow(true);

  return (
    <>
      <Navbar expand="md" className="d-md-none px-3">
        <Button variant="light" className="ms-auto" onClick={handleShow}>
          ☰
        </Button>
      </Navbar>

      <Offcanvas show={show} onHide={handleClose} className="sidebar-offcanvas d-md-none" backdrop>
        <Offcanvas.Header closeButton>
          <div className="sidebar-offcanvas-logo-wrapper">
            <img src={Logo} alt="Logo" className="logo" />
          </div>
        </Offcanvas.Header>
        <Offcanvas.Body>
          <nav className="sidebar-nav">
            {navLinks.map((link, idx) => (
              <a
                key={idx}
                href={`#${link.label.toLowerCase()}`}
                className="sidebar-link"
                onClick={handleClose}
              >
                {link.icon}
                <span>{link.label}</span>
              </a>
            ))}
          </nav>
        </Offcanvas.Body>
      </Offcanvas>

      <div className="sidebar d-none d-md-flex flex-column">
        <div className="sidebar-header">
          <img src={Logo} alt="Logo" className="logo" />
        </div>
        <nav className="sidebar-nav">
          {navLinks.map((link, idx) => (
            <a key={idx} href={`#${link.label.toLowerCase()}`} className="sidebar-link">
              {link.icon}
              <span>{link.label}</span>
            </a>
          ))}
        </nav>
      </div>
    </>
  );
};

export default Sidebar;
