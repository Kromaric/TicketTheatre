import { Box, Text } from "@chakra-ui/react";
import { Link as RouterLink } from "react-router-dom";

type TicketLabelProps = {
  text: string;
  variant?: "red" | "yellow";
  to?: string;  
  onClick?: () => void;  
};

export const TicketLabel = ({
  text,
  variant = "red",
  to,
  onClick,
}: TicketLabelProps) => {
  const isYellow = variant === "yellow";

  const backgroundImage = isYellow
    ? `
      radial-gradient(
        circle at right center,
        transparent 0,
        transparent 12px,
        #FFCC00 12px
      )
    `
    : `
      radial-gradient(
        circle at right center,
        transparent 0,
        transparent 12px,
        #511111 12px
      )
    `;

  const content = (
    <Box
      position="relative"
      display="inline-flex"
      alignItems="center"
      px="6"
      py="2"
      color={isYellow ? "black" : "white"}
      backgroundImage={backgroundImage}
      cursor="pointer"
      transition="transform 0.15s ease-out"
      _hover={{ transform: "scale(1.05)" }}
      onClick={onClick}
    >
      <Text fontWeight="bold" ml="2" fontSize="sm">
        {text}
      </Text>
    </Box>
  );

  if (to) {
    return (
      <RouterLink to={to} style={{ textDecoration: "none" }}>
        {content}
      </RouterLink>
    );
  }

  return content;
};
