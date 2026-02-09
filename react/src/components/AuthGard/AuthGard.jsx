import { useContext, useEffect } from "react";
import { useNavigate } from "react-router";
import { JwtContext } from "../../contexts/JwtContext";

const AuthGard = ({ children }) => {
  const { jwt } = useContext(JwtContext);
  const navigate = useNavigate();

  useEffect(() => {
    if (!jwt) {
      navigate("/");
    }
  }, [jwt, navigate]);

  return { ...children };
};

export default AuthGard;
