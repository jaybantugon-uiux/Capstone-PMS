import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './css/App.css';
import './css/Dashboard.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import Home from './pages/Home';
import SignUp from './pages/SignUp';
import Login from './pages/Login';
import ResetPassword from './pages/ResetPassword';
import ChangePassword from './pages/ChangePassword';
import VerifyEmail from './pages/VerifyEmail';

import AdminDashboard from './dashboard/Admin';
import SiteCoordinatorDashboard from './dashboard/SiteCoordinator';
//import PMDashboard from './dashboard/ProjectManager';

import TaskPage from "./pages/TaskManagement";
import InventoryPage from "./pages/InventoryManagement";
import EquipmentPage from "./pages/EquipmentMonitoring";
import FilePage from "./pages/FileManagement";
import ExpensesPage from "./pages/LiquidatedExpenses";
import ProjectPage from "./pages/ProjectMonitoring";
import UsersPage from "./pages/UserManagement";

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/signup" element={<SignUp />} />
        <Route path="/login" element={<Login />} />
        <Route path="/resetPassword" element={<ResetPassword />} />
        <Route path="/change-password" element={<ChangePassword />} />
        <Route path="/verify-email" element={<VerifyEmail />} />
        <Route path="/admin-dashboard" element={<AdminDashboard />} />
        <Route path="/sc-dashboard" element={<SiteCoordinatorDashboard />} />
        <Route path="/task" element={<TaskPage />} />
        <Route path="/inventory" element={<InventoryPage />} />
        <Route path="/files" element={<FilePage />} />
        <Route path="/expenses" element={<ExpensesPage />} />
        <Route path="/equipment" element={<EquipmentPage />} />
        <Route path="/project" element={<ProjectPage />} />
        <Route path="/users" element={<UsersPage />} />

      </Routes>
    </Router>
  );

}

export default App;