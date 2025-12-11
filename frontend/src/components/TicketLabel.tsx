import { Box, Text } from "@chakra-ui/react";

type TicketLabelProps = {
  text: string;
  variant?: "red" | "yellow"; // red par défaut
};

export const TicketLabel = ({ text, variant = "red" }: TicketLabelProps) => {
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

  return (
    <Box
      position="relative"
      display="inline-flex"
      alignItems="center"
      px="6"
      py="2"
      color={isYellow ? "black" : "white"}
      backgroundImage={backgroundImage}
    >
      <Text fontWeight="bold" ml="2">
        {text}
      </Text>
    </Box>
  );
};
