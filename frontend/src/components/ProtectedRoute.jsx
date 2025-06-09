import { Navigate } from 'react-router-dom';

const ProtectedRoute = ({ children, allowedRoles = [] }) => {
    const user = JSON.parse(localStorage.getItem('user'));
    const token = localStorage.getItem('token');

    if (!token || !user) {
        return <Navigate to="/login" replace />;
    }

    if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        // Redirect to appropriate dashboard based on role
        switch (user.role) {
            case 'admin':
                return <Navigate to="/admin-dashboard" replace />;
            case 'emp':
                return <Navigate to="/employee-dashboard" replace />;
            case 'finance':
                return <Navigate to="/finance-dashboard" replace />;
            case 'pm':
                return <Navigate to="/pm-dashboard" replace />;
            case 'sc':
                return <Navigate to="/sc-dashboard" replace />;
            default:
                return <Navigate to="/client-dashboard" replace />;
        }
    }

    return children;
};

export default ProtectedRoute;