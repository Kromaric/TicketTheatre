import { useState } from "react";
import { Box, Flex } from "@chakra-ui/react";

type SeancesTab = "affiche" | "toutes";

export const SeancesNav = ({
  value,
  defaultValue = "affiche",
  onChange,
}: {
  value?: SeancesTab;
  defaultValue?: SeancesTab;
  onChange?: (tab: SeancesTab) => void;
}) => {
  const [internal, setInternal] = useState<SeancesTab>(defaultValue);
  const active = value ?? internal;

  const setActive = (tab: SeancesTab) => {
    if (value === undefined) setInternal(tab);
    onChange?.(tab);
  };

  const isAffiche = active === "affiche";

  const baseBtn = {
    as: "button" as const,
    type: "button" as const,
    h: "44px",
    px: "5",
    borderRadius: "full",
    fontSize: "0.7rem",
    fontWeight: "semibold",
    textTransform: "uppercase" as const,
    whiteSpace: "nowrap" as const,
    cursor: "pointer",
    transition: "all 240ms ease",
    border: "none",
    outline: "none",
    boxShadow: "none",
    _focus: { outline: "none", boxShadow: "none" },
    _focusVisible: { outline: "none", boxShadow: "none" },
    _active: { outline: "none", boxShadow: "none" },
  };

  return (
    <Flex
      role="tablist"
      aria-label="Navigation séances"
      w="full"
      maxW="420px"
      h="44px"
      align="center"
      gap="10px"
    >

      <Box
        {...baseBtn}
        role="tab"
        aria-selected={isAffiche}
        onClick={() => setActive("affiche")}
        flexGrow={isAffiche ? 1.25 : 1}
        bg="red.800"
        color="white"
        zIndex={isAffiche ? 2 : 1}
        boxShadow={isAffiche ? "lg" : "none"}
        transform={
          isAffiche
            ? "translateX(0) scale(1.08)" 
            : "translateX(-8px) scale(0.93)"
        }
        opacity={isAffiche ? 1 : 0.82}
      >
        A L’AFFICHE
      </Box>

      <Box
        {...baseBtn}
        role="tab"
        aria-selected={!isAffiche}
        onClick={() => setActive("toutes")}
        flexGrow={!isAffiche ? 1.25 : 1}
        bg="yellow.500"
        color="white"
        zIndex={!isAffiche ? 2 : 1}
        boxShadow={!isAffiche ? "lg" : "none"}
        transform={
          !isAffiche
            ? "translateX(0) scale(1.08)" 
            : "translateX(8px) scale(0.93)"
        }
        opacity={!isAffiche ? 1 : 0.82}
      >
        TOUTES LES SÉANCES
      </Box>
    </Flex>
  );
};
