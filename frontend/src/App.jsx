import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './css/App.css';
import './css/Dashboard.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import Home from './pages/Home';
import SignUp from './pages/SignUp';
import Login from './pages/Login';
import ResetPassword from './pages/ResetPassword';
import VerifyEmail from './pages/VerifyEmail';

import AdminDashboard from './dashboard/Admin';
//import PMDashboard from './dashboard/ProjectManager';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/signup" element={<SignUp />} />
        <Route path="/login" element={<Login />} />
        <Route path="/resetPassword" element={<ResetPassword />} />
        <Route path="/verify-email" element={<VerifyEmail />} />
        <Route path="/admin-dashboard" element={<AdminDashboard />} />
      </Routes>
    </Router>
  );

}

export default App;